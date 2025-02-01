<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\ApiResponse\Resources\WishlistProductResource;
use App\Domains\Order\Models\ProductWishlist;
use App\Domains\Products\Models\Product;

/**
 * Class ApiWishlistService.
 */
class ApiWishlistService
{

    public function list($auth_id = null)
    {
        $limit = request('limit', 24);
        $auth_id = $auth_id ?: auth('sanctum')->id();
        $list = ProductWishlist::with(['product'])
            ->whereHas('product', function ($product) {
                $product->where('status', 'publish');
            })
            ->where('user_id', $auth_id)
            ->orderByDesc('id')
            ->paginate($limit);

        return WishlistProductResource::collection($list, ['product'])->response()->getData(true);
    }

    public function addToLove($auth_id)
    {
        $product_uuid = request('uuid');
        $product = Product::query()->where('uuid', $product_uuid)->first();
        $wishlist = ProductWishlist::query()
            ->where('product_id', $product->id)
            ->where('user_id', $auth_id)->first();
        $wishlist = $wishlist ?: new ProductWishlist();
        $wishlist->product_id = $product->id;
        $wishlist->user_id = $auth_id;
        $wishlist->save();
        $wishlist->refresh();
        return $this->list($auth_id);
    }


    public function delete()
    {
        $auth_id = auth('sanctum')->id();
        $uuid = request('uuid');
        ProductWishlist::query()->whereHas('product', function ($product) use ($uuid) {
            $product->where('uuid', $uuid);
        })->where('user_id', $auth_id)->delete();
        return $this->list();
    }
}
