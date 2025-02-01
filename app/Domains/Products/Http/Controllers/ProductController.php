<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Products\Models\Brand;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Models\Supplier;
use App\Domains\Products\Models\Unit;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{

  use ProductsTrait;
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('backend.products.inhouse.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $categories =  Taxonomy::whereNotNull('active')
      ->whereNull('parent_id')
      ->whereNull('type')
      ->pluck('name', 'id');
    $brands = Brand::whereNotNull('active')->pluck('name', 'id');
    $units = Unit::whereNotNull('active')->pluck('name', 'id');
    $suppliers = Supplier::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
    $genSku = $this->generateProductCoreSku();
    return view('backend.products.inhouse.create', compact('categories', 'brands', 'suppliers', 'units', 'genSku'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $this->validateProduct();
    $product = Product::create($data);

    $taxonomy = $this->mergedTaxonomies();
    $product->taxonomies()->sync($taxonomy);


    return redirect()
      ->route('admin.product.inhouse.index')
      ->withFlashSuccess('Product Created successfully');
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $product = Product::with('taxonomies')->findOrFail($id);
    $taxonomies = Taxonomy::whereNotNull('active')->get();

    $brands = Brand::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
    $units = Unit::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
    $suppliers = Supplier::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
    return view('backend.products.inhouse.edit', compact('product', 'taxonomies', 'brands', 'suppliers', 'units'));
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
    $data = $this->validateProduct($id);
    $product = Product::findOrFail($id);
    $product->update($data);
    $taxonomy = $this->mergedTaxonomies();
    $product->taxonomies()->sync($taxonomy);

    return redirect()
      ->route('admin.product.inhouse.index')
      ->withFlashSuccess('Product updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $product = Product::withTrashed()->find($id);
    if ($product->trashed()) {
      $product->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Product permanently deleted',
      ]);
    } else if ($product->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Product moved to trashed successfully',
      ]);
    }

    return \response([
      'status' => false,
      'icon' => 'error',
      'msg' => 'Delete failed',
    ]);
  }

  public function validateProduct($id = 0)
  {
    $req = request()->validate([
      'name' => 'nullable|string|max:255',
      'name_bn' => 'nullable|string|max:255',
      'sku' => 'required|string|max:191|unique:products,sku,' . $id,
      'categories' => 'required|array|exists:taxonomies,id',
      'subcategories' => 'required|array|exists:taxonomies,id',
      'subsubcategories' => 'nullable|array|exists:taxonomies,id',
      'brand_id' => 'nullable|exists:brands,id',

      'sale_price' => 'required|numeric',
      'discount_price' => 'nullable|numeric',
      'purchase_price' => 'nullable|numeric',
      'opening_stock' => 'nullable|numeric',
      'supplier_id' => 'nullable|exists:suppliers,id',

      'purchase_unit' => 'required|exists:units,id',
      'sale_unit' => 'required|exists:units,id',
      'conversion_rate' => 'required|numeric',
      'alert_qty' => 'required|numeric',
      'available' => 'required|string|max:55',
      'order_limit' => 'required|numeric',

      'thumbnail_img' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
      'featured_img' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
      'flash_deal_img' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',

      'gallery_img_one' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
      'gallery_img_two' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
      'gallery_img_three' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
      'gallery_img_four' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',

      'video_provider' => 'nullable|string|max:191',
      'video_link' => 'nullable|string|max:191',

      'meta_title' => 'nullable|string|max:255',
      'meta_description' => 'nullable|string|max:400',
      'meta_img' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',

      'short_description' => 'nullable|string|max:800',
      'description' => 'nullable|string',

    ]);

    unset($req['thumbnail_img'], $req['categories'], $req['subcategories'], $req['subsubcategories'], $req['featured_img'], $req['flash_deal_img'], $req['gallery_img_one'], $req['gallery_img_two'], $req['gallery_img_three'], $req['gallery_img_four'], $req['meta_img']);

    $req['brand_id'] = request('brand_id', null);
    $req['supplier_id'] = request('supplier_id', null);

    if (!$id) {
      $req['user_id'] = auth()->id();
    }

    if (request()->hasFile('thumbnail_img')) {
      $thumbnail_img = request()->file('thumbnail_img');
      $name = 'thumb-' . time();
      $req['thumbnail_img'] = store_picture($thumbnail_img, 'product', $name);
    }

    if (request()->hasFile('featured_img')) {
      $featured_img = request()->file('featured_img');
      $name = 'featured-' . time();
      $req['featured_img'] = store_picture($featured_img, 'product', $name);
    }

    if (request()->hasFile('flash_deal_img')) {
      $flash_deal_img = request()->file('flash_deal_img');
      $name = 'flash_deal-' . time();
      $req['flash_deal_img'] = store_picture($flash_deal_img, 'product', $name);
    }

    $galleryItem = [];
    if (request()->hasFile('gallery_img_one')) {
      $flash_deal_img = request()->file('gallery_img_one');
      $name = 'galleryOne-' . time();
      $galleryItem['gallery_img_one'] = store_picture($flash_deal_img, 'gallery', $name);
    }
    if (request()->hasFile('gallery_img_two')) {
      $flash_deal_img = request()->file('gallery_img_two');
      $name = 'galleryTwo-' . time();
      $galleryItem['gallery_img_two'] = store_picture($flash_deal_img, 'gallery', $name);
    }

    if (request()->hasFile('gallery_img_three')) {
      $flash_deal_img = request()->file('gallery_img_three');
      $name = 'galleryThree-' . time();
      $galleryItem['gallery_img_three'] = store_picture($flash_deal_img, 'gallery', $name);
    }

    if (request()->hasFile('gallery_img_four')) {
      $flash_deal_img = request()->file('gallery_img_four');
      $name = 'galleryFour-' . time();
      $galleryItem['gallery_img_four'] = store_picture($flash_deal_img, 'gallery', $name);
    }

    if (!empty($galleryItem)) {
      $req['photos'] = json_encode($galleryItem);
    }

    return $req;
  }

  public function restore($id)
  {
    Product::onlyTrashed()->findOrFail($id)->restore();
    return redirect()->route('admin.product.inhouse.index')
      ->withFlashSuccess('Product Recovered Successfully');
  }



  public function ajaxImageUpload()
  {
    $data['location'] =  '';
    if (request()->hasFile('file')) {
      $file = request()->file('file');
      $name = 'upload-' . time();
      $data['location'] = '/' . store_picture($file, 'editor', $name);
    }
    return response($data);
  }
}
