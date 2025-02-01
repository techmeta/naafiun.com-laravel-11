<?php

namespace App\Domains\Cart\Models;

use App\Domains\Auth\Models\User;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use Uuid, HasFactory;

    protected $table = 'customer_shipping_address';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $hidden = ['user_id', 'created_at', 'updated_at', 'deleted_at'];

    protected $fillable = ['name', 'company', 'phone', 'tax_id', 'zip_code', 'city', 'state', 'country', 'address', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
