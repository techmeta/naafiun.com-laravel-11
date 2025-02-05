<?php

namespace App\Domains\Products\Models\Book;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookPublisher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'boi_publishers';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'boi_book_publisher', 'publisher_id', 'book_id');
    }
}
