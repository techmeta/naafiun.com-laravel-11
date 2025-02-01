<?php

namespace App\Domains\Products\Http\Controllers\Book;

use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Models\Book\BookTranslator;
use App\Domains\Products\Traits\BookTrait;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TranslatorController extends Controller
{

  use BookTrait, ProductsTrait;

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('backend.products.book.translator.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('backend.products.book.translator.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $this->validateTranslator();
    // dd($data);
    BookTranslator::create($data);

    return redirect()
      ->route('admin.product.book.translator.index')
      ->withFlashSuccess('Translator Created successfully');
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
    $translator = BookTranslator::findOrFail($id);
    return view('backend.products.book.translator.edit', compact('translator'));
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
    $data = $this->validateTranslator($id);
    $translator = BookTranslator::find($id);
    if ($translator) {
      $translator->update($data);
    }

    return redirect()
      ->route('admin.product.book.translator.index')
      ->withFlashSuccess('Translator updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $translator = BookTranslator::withTrashed()->find($id);
    if ($translator->trashed()) {
      $translator->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Translator permanently deleted',
      ]);
    } else if ($translator->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Translator moved to trashed successfully',
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
    return view('backend.products.book.inhouse.trash');
  }


  public function validateTranslator($id = 0)
  {

    $data = request()->validate([
      'name' => 'required|nullable|string|max:191',
      'slug' => 'nullable',
      'top' => 'nullable|string|max:255',
      'meta_title' => 'nullable|string|max:100',
      'meta_description' => 'nullable|string|max:400',
      'logo' => 'nullable',
      'active' => 'nullable'

    ]);

    if (!$id) {
      $data['user_id'] = auth()->id();
    }

    if (request()->hasFile('logo')) {
      $logo = request()->file('logo');
      $data['logo'] = store_picture($logo, 'publisher');
    }

    return $data;
  }
}
