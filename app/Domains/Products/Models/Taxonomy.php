<?php

namespace App\Domains\Products\Models;

use App\Domains\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Taxonomy extends Model
{
    use Searchable, HasFactory, SoftDeletes;

    protected $table = 'taxonomies';

    protected $guarded = [];

    public $primaryKey = 'id';

    public $timestamps = true;

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            // Add other searchable attributes
        ];
    }


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'taxonomy_product', 'product_id', 'taxonomy_id');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Taxonomy::class, 'parent_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
