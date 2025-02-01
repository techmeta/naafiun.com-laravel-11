<?php

namespace App\Domains\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerify extends Model
{
    use HasFactory;

    protected $table = 'otp_verify';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

}
