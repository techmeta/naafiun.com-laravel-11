<?php

namespace App\Domains\Settings\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    protected $hidden = ['id', 'user_id', 'active', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
