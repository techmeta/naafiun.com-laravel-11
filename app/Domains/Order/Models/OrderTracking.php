<?php

namespace App\Domains\Order\Models;

use App\Domains\Auth\Models\User;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderTracking extends Model
{
    use Uuid, SoftDeletes;

    protected $table = 'order_tracking';
    public $primaryKey = 'id';
    public $timestamps = true;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

}
