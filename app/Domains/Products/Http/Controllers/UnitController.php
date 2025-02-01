<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Products\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Str;

class UnitController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('backend.products.unit.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('backend.products.unit.create');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $data = $this->validateBrands();
    Unit::create($data);
    return redirect()
      ->route('admin.product.unit.index')
      ->withFlashSuccess('Unit created successfully');
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
    $unit = Unit::findOrFail($id);
    return view('backend.products.unit.edit', compact('unit'));
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
    $data = $this->validateBrands($id);
    $unit = Unit::find($id);
    if ($unit) {
      $unit->update($data);
    }
    return redirect()
      ->route('admin.product.unit.index')
      ->withFlashSuccess('Unit updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $unit = Unit::withTrashed()->find($id);
    if ($unit->trashed()) {
      $unit->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Unit permanently deleted',
      ]);
    } else if ($unit->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Unit moved to trashed successfully',
      ]);
    }

    return \response([
      'status' => false,
      'icon' => 'error',
      'msg' => 'Delete failed',
    ]);
  }


  public function validateBrands($id = 0)
  {
    $data = request()->validate([
      'active' => 'nullable|string|max:191',
      'name' => 'required|string|max:191|unique:units,name,' . $id,
      'description' => 'nullable|string|max:255',
    ]);

    $data['active'] = request('active', null);
    $data['slug'] = Str::slug($data['name']);

    if (!$id) {
      $data['user_id'] = auth()->id();
    }


    return $data;
  }



  public function trash()
  {
    return view('backend.products.unit.trash');
  }

  public function restore($id)
  {
    Unit::onlyTrashed()->findOrFail($id)->restore();
    return redirect()->route('admin.product.unit.index')
      ->withFlashSuccess('Unit Recovered Successfully');
  }
}
