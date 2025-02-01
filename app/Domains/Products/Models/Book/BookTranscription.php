<?php

namespace App\Domains\Products\Models\Book;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookTranscription extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'boi_transcriptions';

  public $primaryKey = 'id';

  public $timestamps = true;

  protected $guarded = [];


  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
