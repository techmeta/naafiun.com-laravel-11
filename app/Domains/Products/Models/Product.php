<?php

namespace App\Domains\Products\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'products';

  public $primaryKey = 'id';

  public $timestamps = true;

  protected $guarded = [];

  public function taxonomies()
  {
    return $this->belongsToMany(Taxonomy::class, 'taxonomy_product', 'product_id', 'taxonomy_id');
  }

  public function Subcategory()
  {
    return $this->belongsTo(SubCategory::class, 'subcategory_id', 'id');
  }

  public function saleUnit()
  {
    return $this->belongsTo(Unit::class, 'sale_unit', 'id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
