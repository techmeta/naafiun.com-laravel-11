<?php

namespace App\Domains\Order\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Products\Models\Product;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReturnItem extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'order_return_items';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(OrderReturnItemVariation::class, 'return_item_id', 'id');
    }


}
