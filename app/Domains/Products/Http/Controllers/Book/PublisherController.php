<?php

namespace App\Domains\Products\Http\Controllers\Book;

use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Traits\BookTrait;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublisherController extends Controller
{

  use BookTrait, ProductsTrait;

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('backend.products.book.publishers.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('backend.products.book.publishers.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $this->validatePublisher();
    // dd($data);
    BookPublisher::create($data);

    return redirect()
      ->route('admin.product.book.publishers.index')
      ->withFlashSuccess('Publishers Created successfully');
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {

    $publisher = BookPublisher::findOrFail($id);
    return view('backend.products.book.publishers.edit', compact('publisher'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $data = $this->validatePublisher($id);
    $publisher = BookPublisher::find($id);
    if ($publisher) {
      $publisher->update($data);
    }

    return redirect()
      ->route('admin.product.book.publishers.index')
      ->withFlashSuccess('Publisher updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $publisher = BookPublisher::withTrashed()->find($id);
    if ($publisher->trashed()) {
      $publisher->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Publisher permanently deleted',
      ]);
    } else if ($publisher->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Publisher moved to trashed successfully',
      ]);
    }

    return \response([
      'status' => false,
      'icon' => 'error',
      'msg' => 'Delete failed',
    ]);
  }



  public function validatePublisher($id = 0)
  {
    $data = request()->validate([
      'name' => 'required|nullable|string|max:191',
      'slug' => 'nullable',
      'top' => 'nullable|string|max:255',
      'meta_title' => 'nullable|string|max:100',
      'meta_description' => 'nullable|string|max:400',
      'picture' => 'nullable',
      'active' => 'nullable'
    ]);
    unset($picture);
    if (!$id) {
      $data['user_id'] = auth()->id();
    }

    $data['active'] = request('active') ? now() : null;
      $data['top'] = request('top', null);

    if (request()->hasFile('picture')) {
      $logo = request()->file('picture');
      $data['picture'] = store_picture($logo, 'publisher');
    }

    return $data;
  }
}
