<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findByVendor(int $vendorId): Collection
    {
        return $this->model->whereHas('orderItems.productVariant.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->get();
    }
}