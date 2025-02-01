<?php

namespace App\Domains\Products\Models;

use App\Domains\Auth\Models\User;
use App\Domains\Products\Models\Book\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentView extends Model
{
    use HasFactory;

    protected $table = 'recent_views';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Book::class, 'product_id', 'id');
    }
}
