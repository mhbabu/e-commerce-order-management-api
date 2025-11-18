<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

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
}
