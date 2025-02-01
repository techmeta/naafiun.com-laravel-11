<?php

namespace App\Domains\Cart\Models;

use App\Models\Traits\Uuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCart extends Model
{
    use Uuid, HasFactory;

    protected $table = 'cart';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerCartItem::class, 'cart_id', 'id');
    }
}
