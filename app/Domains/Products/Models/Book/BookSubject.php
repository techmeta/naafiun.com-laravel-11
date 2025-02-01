<?php

namespace App\Domains\Products\Models\Book;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookSubject extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'boi_subjects';

  public $primaryKey = 'id';

  public $timestamps = true;

  protected $guarded = [];


  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function books()
  {
    return $this->belongsToMany(Book::class, 'boi_book_subjects', 'subject_id', 'book_id');
  }
}
