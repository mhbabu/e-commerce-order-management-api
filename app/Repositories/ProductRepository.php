<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use PDO;

class ProductRepository extends BaseRepository
{
    protected array $searchableColumns = [ // for safe search
        'name',
        'sku',
        'category',
        'description'
    ];

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function list(array $filters, $authUser)
    {
        $query = $this->model->with('variants.inventory');

        if ($authUser->role === 'vendor') {
            $query->where('vendor_id', $authUser->id);
        }
        if ($authUser->role === 'customer') {
            $query->where('is_active', true);
        }

        if (!empty($filters['search']) && !empty($filters['search_by'])) {
            $searchText = $filters['search'];
            $columns = is_array($filters['search_by']) ? $filters['search_by'] : [$filters['search_by']];

            $query->where(function ($q) use ($columns, $searchText) {
                foreach ($columns as $column) {
                    if (in_array($column, $this->searchableColumns)) {
                        $q->orWhere($column, 'like', "%{$searchText}%");
                    }
                }
            });
        }


        if (!empty($filters['sort_by'])) { // we can do it also multiple like search
            $query->orderBy($filters['sort_by'], $filters['sort_order']);
        }

        $page    = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }


    public function findActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Bulk import products from CSV using staging table
     */

    /**
     * Bulk import products from CSV using staging table
     */
    public function bulkImport($file, int $vendorId = 1): array
    {
        $counts = [
            'products'    => 0,
            'variants'    => 0,
            'inventories' => 0,
        ];

        try {
            $path = $file->getRealPath();
            $escapedPath = escapeshellarg($path);

            // Enable LOCAL INFILE for this connection
            DB::connection()->getPdo()->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

            // Set UTF8MB4 charset
            DB::statement('SET NAMES utf8mb4'); // used for special characters insert like emoji 

            // Detect header row
            $firstLine = fgets(fopen($path, 'r'));
            $headerExists = preg_match('/product_name/i', trim($firstLine));

            // Clear staging table befor inserting
            DB::table('product_import_staging')->truncate();

            // Load CSV into staging table with correct column mapping
            DB::statement("
            LOAD DATA LOCAL INFILE $escapedPath
            INTO TABLE product_import_staging
            FIELDS TERMINATED BY ',' 
            ENCLOSED BY '\"'
            LINES TERMINATED BY '\\n'
            " . ($headerExists ? "IGNORE 1 ROWS" : "") . "
            (
                product_name,
                description,
                base_price,
                category,
                product_sku,      -- products table sku
                variant_color,
                variant_storage,
                price_modifier,
                variant_sku,       -- variants table sku
                quantity,
                low_stock_threshold
            )
        ");

            // Clean numeric values (remove commas)
            DB::statement("
            UPDATE product_import_staging
            SET 
                base_price = REPLACE(base_price, ',', ''),
                price_modifier = REPLACE(price_modifier, ',', '')
        ");

            // Trim all string fields
            DB::statement("
            UPDATE product_import_staging
            SET 
                product_name = TRIM(product_name),
                description = TRIM(description),
                category = TRIM(category),
                product_sku = TRIM(product_sku),
                variant_color = TRIM(variant_color),
                variant_storage = TRIM(variant_storage),
                variant_sku = TRIM(variant_sku)
        ");

            //Insert/Update Products
            DB::statement("
            INSERT INTO products (name, description, base_price, category, sku, vendor_id, created_at, updated_at)
            SELECT product_name, description, base_price, category, product_sku, ?, NOW(), NOW()
            FROM product_import_staging
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                description = VALUES(description),
                base_price = VALUES(base_price),
                category = VALUES(category),
                vendor_id = VALUES(vendor_id),
                updated_at = NOW()
        ", [$vendorId]);

            $counts['products'] = DB::table('products')->count();

            // Insert Variants
            DB::statement("
            INSERT IGNORE INTO product_variants (product_id, price_modifier, sku, attributes, created_at, updated_at)
            SELECT 
                p.id,
                s.price_modifier,
                s.variant_sku,  -- variant_sku correctly mapped to variants table sku
                JSON_OBJECT('color', s.variant_color, 'storage', s.variant_storage),
                NOW(),
                NOW()
            FROM product_import_staging s
            JOIN products p ON p.sku = s.product_sku
        ");

            $counts['variants'] = DB::table('product_variants')->count();

            // Insert Inventories
            DB::statement("
            INSERT IGNORE INTO inventories (product_variant_id, quantity, low_stock_threshold, created_at, updated_at)
            SELECT 
                pv.id,
                s.quantity,
                s.low_stock_threshold,
                NOW(),
                NOW()
            FROM product_import_staging s
            JOIN product_variants pv ON pv.sku = s.variant_sku
        ");

            $counts['inventories'] = DB::table('inventories')->count();
        } catch (\Exception $e) {
            throw new \Exception('Failed to import CSV: ' . $e->getMessage());
        }

        return $counts;
    }


    //We can first insert data into product_import_staging table, then use a scheduled task to populate products, variants, and inventory based on specific conditions
    public function bulkImport1($file, int $vendorId = 1): int
    {
        $path = $file->getRealPath();
        $escapedPath = escapeshellarg($path);

        // Enable LOCAL INFILE for this connection
        DB::connection()->getPdo()->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

        try {
            // Set UTF8MB4 charset
            DB::statement('SET NAMES utf8mb4');

            // Load CSV into product_import_staging
            DB::statement("
            LOAD DATA LOCAL INFILE $escapedPath
            INTO TABLE product_import_staging
            FIELDS TERMINATED BY ','
            ENCLOSED BY '\"'
            LINES TERMINATED BY '\\n'
            IGNORE 1 ROWS
            (
                product_name,
                description,
                base_price,
                category,
                product_sku,
                variant_color,
                variant_storage,
                price_modifier,
                variant_sku,
                quantity,
                low_stock_threshold
            )
        ");

            return true; // Successfully imported into staging

        } catch (\Exception $e) {
            throw new \Exception('Failed to import CSV into staging: ' . $e->getMessage());
        }
    }
}
