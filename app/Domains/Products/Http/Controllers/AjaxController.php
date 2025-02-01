<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Products\Models\Book\Book;
use App\Http\Controllers\Controller;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Products\Traits\ProductsTrait;

class AjaxController extends Controller
{

  use ProductsTrait;

  public function generateProductSku()
  {
    $type = request('type');
    $genSku = $this->generateProductCoreSku($type);
    return response([
      'sku' => $genSku
    ]);
  }

  public function getSubcategories()
  {
    $categories = request('categories', []);
    if (!empty($categories)) {
      $subcategories = Taxonomy::whereNotNull('active')
        ->where('type', 'subcategory')
        ->whereIn('parent_id', $categories)
        ->get();
      $data = [];
      foreach ($subcategories as $key => $item) {
        $data[$key] = [
          'id' => $item->id,
          'text' => $item->name
        ];
      }
      return response([
        'results' => $data
      ]);
    }
    return response(['results' => []]);
  }

  public function getSubSubcategories()
  {
    $data = $this->getActiveSubSubCategories();
    return response([
      'results' => $data
    ]);
  }


  public function addSubSubcategories()
  {
    $taxonomies = Taxonomy::whereNotNull("active")->get();
    $parents = $taxonomies->whereNull("parent_id")->pluck("name", "id");
    $parent = $taxonomies->whereNull("parent_id")->first();
    $parent_id = $parent ? $parent->id : null;
    $subParents = $taxonomies->where('parent_id', $parent_id)
      ->where("type", "subcategory")
      ->pluck("name", "id");
    return response([
      'view' => view('backend.products.taxonomy.ajax.create-sub-sub-category', compact('parents', 'subParents'))->render()
    ]);
  }

  public function addSubcategories()
  {
    $parents = Taxonomy::whereNotNull("active")
      ->whereNull("parent_id")->pluck("name", "id");
    return response([
      'view' => view('backend.products.taxonomy.ajax.create-sub-category', compact('parents'))->render()
    ]);
  }

  public function addCategories()
  {
    return response([
      'view' => view('backend.products.taxonomy.ajax.create-category')->render()
    ]);
  }

  public function storeSubSubcategories()
  {
    $data = $this->validateTaxonomies();
    Taxonomy::create($data);
    return response([
      'results' => true
    ]);
  }

  public function changeBookStatus()
  {
    $type = request('type');
    $id = request('id');
    $value = request('value');

    $book = Book::find($id);
    if ($type == 'is_new') {
      $book->update([
        'is_new' => $value != 'new' ? now() : null
      ]);
    }

    if ($type == 'available') {
      $book->update([
        'available' => $value == 'yes' ? 'no' : 'yes'
      ]);
    }

    $book = Book::select('is_new', 'available')->find($id);
    if ($book->is_new) {
      $newButton =  '<a href="' . route('admin.product.book.status', ['type' => 'is_new', 'id' => $id, 'value' => 'new']) . '" class="badge badge-success control-book-status" title="Remove from New">New</a>';
    } else {
      $newButton = '<a href="' . route('admin.product.book.status', ['type' => 'is_new', 'id' => $id, 'value' => 'no']) . '" class="badge badge-danger control-book-status" title="Make as New">No</a>';
    }

    if ($book->available == 'yes') {
      $availableButton =  '<a href="' . route('admin.product.book.status', ['type' => 'available', 'id' => $id, 'value' => 'yes']) . '" class="badge badge-success control-book-status" title="Toggle Available">Yes</a>';
    } else {
      $availableButton = '<a href="' . route('admin.product.book.status', ['type' => 'available', 'id' => $id, 'value' => 'no']) . '" class="badge badge-danger control-book-status" title="Toggle Available">No</a>';
    }


    return response([
      'newButton' => $newButton,
      'availableButton' => $availableButton,
    ]);
  }
}
