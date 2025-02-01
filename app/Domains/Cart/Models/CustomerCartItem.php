<?php

namespace App\Domains\Cart\Models;

use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Product;
use App\Models\Traits\Uuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCartItem extends Model
{
    use Uuid, HasFactory;

    protected $table = 'cart_item';

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

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'product_id', 'id');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(CustomerCartItemVariation::class, 'cart_item_id', 'id');
    }
}
