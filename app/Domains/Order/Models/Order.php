<?php

namespace App\Domains\Order\Models;

use App\Domains\Order\Events\TrackingOrderEvent;
use App\Models\Traits\Uuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    public $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();
        self::created(function (Model $model) {
            event(new TrackingOrderEvent($model));
        });
        self::updated(function (Model $model) {
            event(new TrackingOrderEvent($model));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function returnOrder(): HasOne
    {
        return $this->hasOne(OrderReturn::class, 'order_id', 'id');
    }
}
