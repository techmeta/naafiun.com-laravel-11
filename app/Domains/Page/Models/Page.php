<?php

namespace App\Domains\Page\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'pages';

  public $primaryKey = 'id';

  public $timestamps = true;

  protected $guarded = [];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
