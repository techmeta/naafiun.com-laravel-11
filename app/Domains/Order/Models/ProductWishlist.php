<?php

namespace App\Domains\Order\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Products\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWishlist extends Model
{
  use HasFactory;

  protected $table = 'product_wishlist';

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
}
