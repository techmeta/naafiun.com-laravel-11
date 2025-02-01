<?php

namespace App\Domains\Products\Traits;

use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Models\Taxonomy;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

trait ProductsTrait
{

  public function generateProductCoreSku($type = null)
  {
    if ($type == "book") {
      $product = Book::latest()->first();
    } else {
      $product = Product::latest()->first();
    }
    $nextProductId = $product ? ($product->id + 1) : 1;
    return 'naaf-' . generate_zero_prefix_number($nextProductId, 7);
  }

  public function mergedTaxonomies()
  {
    $taxonomy = request('categories', []);
    $subcategories = request('subcategories', []);
    $subsubcategories = request('subsubcategories', []);
    $taxonomy = array_merge($taxonomy, $subcategories);
    return array_merge($taxonomy, $subsubcategories);
  }


  public function validateTaxonomies($id = 0)
  {
    $validParent = $id ? 'exists:taxonomies,id,' . $id : 'exists:taxonomies,id';
    $validSlug = $id ? 'unique:taxonomies,slug,' . $id : 'unique:taxonomies,slug';
    $data = request()->validate([
      'active' => 'nullable|date:Y-m-d H:i:s',
      'name' => 'required|string|max:255',
      'slug' => 'required|string|max:255|' . $validSlug,
      'parent_id' => 'nullable|exists:taxonomies,id',
      'type' => 'nullable|string|max:191',
      'meta_title' => 'nullable|string|max:255',
      'meta_description' => 'nullable|string|max:400',
      'banner' => 'nullable|max:1800|mimes:jpeg,jpg,png,gif,webp',
      'icon' => 'nullable|max:1000|mimes:jpeg,jpg,png,gif,webp',
    ]);

    unset($data['icon'], $data['banner']);

    $data['active'] = request('active', null);
    $data['featured'] = request('featured', null);
    $data['top'] = request('top', false);
    $data['parent_id'] = request('parent_id', null);
    $data['type'] = request('type', null);

    $data['slug'] = Str::slug($data['slug']);

    if (!$id) {
      $data['user_id'] = auth()->id();
    }

    if (request()->hasFile('banner')) {
      $logo = request()->file('banner');
      $data['banner'] = store_picture($logo, 'banner');
    }
    if (request()->hasFile('icon')) {
      $logo = request()->file('icon');
      $data['icon'] = store_picture($logo, 'icon');
    }

    return $data;
  }



  public function getActiveSubSubCategories()
  {
    $data = [];
    $subcategories = request('subcategories', []);
    if (!empty($subcategories)) {
      $subcategories = Taxonomy::whereNotNull('active')
        ->where('type', 'subsubcategory')
        ->whereIn('parent_id', $subcategories)
        ->get();
      foreach ($subcategories as $key => $item) {
        $data[$key] = [
          'id' => $item->id,
          'text' => $item->name
        ];
      }
    }
    return $data;
  }
}
