<?php

namespace App\Repositories\Product;

use App\Models\Order;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository extends BaseRepository
{
    // Columns allowed for safe searching
    protected array $searchableColumns = [
        'order_number',
        'status',
        'user_id',
    ];

    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Return query builder for orders by user
     */
    public function getByUserQuery(int $userId): Builder
    {
        return $this->model->where('user_id', $userId);
    }

    /**
     * Return query builder for orders by status
     */
    public function getByStatusQuery(?string $status = null): Builder
    {
        $query = $this->model->query();
        if ($status) {
            $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Return query builder for orders by vendor
     */
    public function getByVendorQuery(int $vendorId): Builder
    {
        return $this->model->whereHas('orderItems.productVariant.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        });
    }

    /**
     * Apply search on allowed columns
     */
    public function applySearch(Builder $query, ?string $search, array $columns = []): Builder
    {
        $columns = $columns ?: $this->searchableColumns;

        if ($search) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return $query;
    }

    /**
     * Apply search inside order items (product_variant/product)
     */
    public function applyOrderItemSearch(Builder $query, ?string $search): Builder
    {
        if ($search) {
            $query->whereHas('orderItems.productVariant.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
