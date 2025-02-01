<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Products\Models\Taxonomy;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class TaxonomyController extends Controller
{
  use ProductsTrait;
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $categories = Taxonomy::get();
    return view('backend.products.taxonomy.index', compact('categories'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $type = request('type');
    if ($type == "subcategory") {
      $parents = Taxonomy::whereNotNull("active")->whereNull("parent_id")->pluck("name", "id");
      return view('backend.products.taxonomy.subcategory.create', compact('parents'));
    } elseif ($type == "subsubcategory") {
      $taxonomies = Taxonomy::whereNotNull("active")->get();
      $parents = $taxonomies->whereNull("parent_id")->pluck("name", "id");
      $parent = $taxonomies->whereNull("parent_id")->first();
      $parent_id = $parent ? $parent->id : null;
      $subParents = $taxonomies->where('parent_id', $parent_id)
        ->where("type", "subcategory")
        ->pluck("name", "id");
      return view('backend.products.taxonomy.subsubcategory.create', compact('parents', 'subParents'));
    }

    return view('backend.products.taxonomy.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $type = request('type');
    $data = $this->validateTaxonomies();
    Taxonomy::create($data);

    $route = route('admin.product.taxonomy.index');
    if ($type) {
      $route = route('admin.product.taxonomy.index', ['type' => $type]);
    }

    return redirect($route)
      ->withFlashSuccess('Taxonomy created successfully');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $type = request('type');
    $taxonomy = Taxonomy::findOrFail($id);
    if ($type == "subcategory") {
      $parents = Taxonomy::whereNotNull("active")->whereNull("parent_id")->pluck("name", "id");
      return view('backend.products.taxonomy.subcategory.edit', compact('parents', 'taxonomy'));
    } elseif ($type == "subsubcategory") {
      $taxonomies = Taxonomy::whereNotNull("active")->get();
      $parents = $taxonomies->whereNull("parent_id")->pluck("name", "id");
      $parent_parent_id = $taxonomy->parent->parent_id ?? null;
      $subParents = $taxonomies->where('parent_id', $parent_parent_id)
        ->where("type", "subcategory")
        ->pluck("name", "id");
      return view('backend.products.taxonomy.subsubcategory.edit', compact('parents', 'parent_parent_id', 'subParents', 'taxonomy'));
    }
    return view('backend.products.taxonomy.edit', compact('taxonomy'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $type = request('type');
    $data = $this->validateTaxonomies($id);
    $category = Taxonomy::find($id);
    if ($category) {
      $category->update($data);
    }
    $route = route('admin.product.taxonomy.index');
    if ($type) {
      $route = route('admin.product.taxonomy.index', ['type' => $type]);
    }

    return redirect($route)
      ->withFlashSuccess('Taxonomy Updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $taxonomy = Taxonomy::withTrashed()->find($id);
    if ($taxonomy->trashed()) {
      $taxonomy->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Taxonomy permanently deleted',
      ]);
    } else if ($taxonomy->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Taxonomy moved to trashed successfully',
      ]);
    }

    return \response([
      'status' => false,
      'icon' => 'error',
      'msg' => 'Delete failed',
    ]);
  }

  public function trash()
  {
    return view('backend.products.taxonomy.trash');
  }

  public function restore($id)
  {
    $taxonomy = Taxonomy::onlyTrashed()->findOrFail($id);
    $taxonomy->restore();
    return redirect()->route('admin.product.taxonomy.index')
      ->withFlashSuccess($taxonomy->name . ' Recovered Successfully');
  }
}
