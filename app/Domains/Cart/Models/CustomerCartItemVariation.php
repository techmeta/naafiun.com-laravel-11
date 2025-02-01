<?php

namespace App\Domains\Cart\Models;

use App\Models\Traits\Uuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCartItemVariation extends Model
{
    use Uuid, HasFactory;

    protected $table = 'cart_item_variation';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems(): BelongsTo
    {
        return $this->belongsTo(CustomerCartItem::class, 'cart_item_id', 'id');
    }


}
