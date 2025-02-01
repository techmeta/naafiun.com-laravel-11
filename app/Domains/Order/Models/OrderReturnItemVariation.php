<?php

namespace App\Domains\Order\Models;

use App\Domains\Auth\Models\User;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReturnItemVariation extends Model
{
    use Uuid, HasFactory, SoftDeletes;

    protected $table = 'order_return_item_variation';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function return(): BelongsTo
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id', 'id');
    }

    public function returnItem(): BelongsTo
    {
        return $this->belongsTo(OrderReturnItem::class, 'return_item_id', 'id');
    }

}
