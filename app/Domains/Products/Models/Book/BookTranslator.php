<?php

namespace App\Domains\Products\Models\Book;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookTranslator extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'boi_translators';

  public $primaryKey = 'id';

  public $timestamps = true;

  protected $guarded = [];


  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function books()
  {
    return $this->belongsToMany(Book::class, 'boi_book_translators', 'translator_id', 'book_id');
  }
}
