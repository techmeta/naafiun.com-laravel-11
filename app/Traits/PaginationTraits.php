<?php

namespace App\Traits;


trait PaginationTraits
{
    public function getPaginatedData($query, $column = [])
    {
        $limit = request('limit', 20);
        $search_val = request('search');

        if ($search_val && count($column) > 0) {
            return $query->where(function ($query) use ($column, $search_val) {
                $init = 1;
                foreach ($column as $col) {
                    if ($init == 1) {
                        $query->where($col, 'LIKE', '%' . $search_val . '%');
                    } else {
                        $query->orWhere($col, 'LIKE', '%' . $search_val . '%');
                    }
                    $init++;
                }
            })
                ->paginate($limit);
        }

        return $query->orderByDesc('id')
            ->paginate($limit);
    }

}
