<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProductRepository extends BaseRepository
{
    protected array $searchableColumns = [ // for safe search
        'name',
        'sku',
        'category', // instead of category_name
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
    public function bulkImport($file, int $vendorId = 1): int
    {
        $importedCount = 0;

        try {
            $path = $file->getRealPath();
            $escapedPath = escapeshellarg($path);

            if (!ini_get('mysqli.allow_local_infile')) {
                throw new \Exception("LOCAL INFILE not enabled on MySQL server and you can set by running 'SET GLOBAL local_infile=ON;'");

                // SET GLOBAL local_infile = ON;
                // SHOW VARIABLES LIKE 'local_infile';
                // mysql -u root -p -e "SET GLOBAL local_infile = 1;"
                // mysql -u root -p -e "SHOW VARIABLES LIKE 'local_infile';"

            }

            // 1 Check if CSV has header
            $firstLine = fgets(fopen($path, 'r'));
            $headerExists = preg_match('/product_name/i', $firstLine);

            // 2 Clear staging table
            DB::table('product_import_staging')->truncate();

            // 3 Load CSV into staging table
            DB::statement("
                LOAD DATA LOCAL INFILE $escapedPath
                INTO TABLE product_import_staging
                FIELDS TERMINATED BY ','
                ENCLOSED BY '\"'
                LINES TERMINATED BY '\\n'
                " . ($headerExists ? "IGNORE 1 ROWS" : "") . "
            ");

            // 4 Insert into products
            $importedCount = DB::table('products')
                ->insertUsing(
                    ['name', 'description', 'base_price', 'category', 'sku', 'vendor_id', 'created_at', 'updated_at'],
                    DB::table('product_import_staging')
                        ->selectRaw("product_name, description, base_price, category, product_sku, ?, NOW(), NOW()", [$vendorId])
                );

            // 5 Insert into product_variants
            DB::statement("
                INSERT IGNORE INTO product_variants (product_id, price_modifier, sku, attributes, created_at, updated_at)
                SELECT
                    p.id,
                    s.price_modifier,
                    s.variant_sku,
                    JSON_OBJECT('color', s.variant_color, 'storage', s.variant_storage),
                    NOW(),
                    NOW()
                FROM product_import_staging s
                JOIN products p ON p.sku = s.product_sku
            ");

            // 6 Insert into inventories
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
        } catch (\Exception $e) {
            throw new \Exception('Failed to import CSV: ' . $e->getMessage());
        }

        return $importedCount;
    }

    public function bulkImport2($file, int $vendorId = 1): int
    {
        $importedCount = 0;

        try {
            $path = $file->getRealPath();
            if (!$path || !file_exists($path)) {
                throw new \Exception('CSV file not found.');
            }

            $escapedPath = escapeshellarg($path);

            // 1️⃣ Check if CSV has header
            $handle = fopen($path, 'r');
            $firstLine = fgets($handle);
            fclose($handle);
            $headerExists = preg_match('/product_name/i', $firstLine);

            // 2️⃣ Clear staging table
            DB::table('product_import_staging')->truncate();

            // 3️⃣ Load CSV into staging table
            if (ini_get('mysqli.allow_local_infile')) {
                // PHP LOAD DATA LOCAL INFILE
                DB::statement("
                LOAD DATA LOCAL INFILE $escapedPath
                INTO TABLE product_import_staging
                FIELDS TERMINATED BY ','
                ENCLOSED BY '\"'
                LINES TERMINATED BY '\\n'
                " . ($headerExists ? "IGNORE 1 ROWS" : "") . "
            ");
            } else {
                // MySQL CLI fallback
                $dbHost = env('DB_HOST', '127.0.0.1');
                $dbPort = env('DB_PORT', 3306);
                $dbName = env('DB_DATABASE');
                $dbUser = env('DB_USERNAME');
                $dbPass = env('DB_PASSWORD');

                // Build SQL command safely
                $sql = "
                LOAD DATA LOCAL INFILE $escapedPath
                INTO TABLE product_import_staging
                FIELDS TERMINATED BY ','
                ENCLOSED BY '\"'
                LINES TERMINATED BY '\\n'
                " . ($headerExists ? "IGNORE 1 ROWS" : "") . ";
            ";

                // Get full path to MySQL CLI
                $mysqlPath = trim(shell_exec('which mysql'));
                if (!$mysqlPath) {
                    throw new \Exception('MySQL CLI not found on server.');
                }

                // Build CLI command
                $cmd = sprintf(
                    '%s -h %s -P %s -u %s -p%s --local-infile=1 %s -e %s 2>&1',
                    escapeshellcmd($mysqlPath),
                    escapeshellarg($dbHost),
                    escapeshellarg($dbPort),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($sql)
                );

                exec($cmd, $output, $returnVar);

                if ($returnVar !== 0) {
                    throw new \Exception('Failed to import CSV via MySQL CLI: ' . implode("\n", $output));
                }
            }

            // 4️⃣ Insert into products
            $importedCount = DB::table('products')
                ->insertUsing(
                    ['name', 'description', 'base_price', 'category', 'sku', 'vendor_id', 'created_at', 'updated_at'],
                    DB::table('product_import_staging')
                        ->selectRaw("product_name, description, base_price, category, product_sku, ?, NOW(), NOW()", [$vendorId])
                );

            // 5️⃣ Insert into product_variants
            DB::statement("
            INSERT IGNORE INTO product_variants (product_id, price_modifier, sku, attributes, created_at, updated_at)
            SELECT
                p.id,
                s.price_modifier,
                s.variant_sku,
                JSON_OBJECT('color', s.variant_color, 'storage', s.variant_storage),
                NOW(),
                NOW()
            FROM product_import_staging s
            JOIN products p ON p.sku = s.product_sku
        ");

            // 6️⃣ Insert into inventories
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
        } catch (\Exception $e) {
            throw new \Exception('Failed to import CSV: ' . $e->getMessage());
        }

        return $importedCount;
    }
}
