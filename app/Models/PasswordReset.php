<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PasswordReset.
 */
class PasswordReset extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    public $primaryKey = 'token';
    public $timestamps = false;

    protected $fillable = ['email', 'token', 'created_at'];
}
