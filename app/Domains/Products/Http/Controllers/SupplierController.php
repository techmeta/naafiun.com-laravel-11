<?php

namespace App\Domains\Products\Http\Controllers;

use App\Domains\Products\Models\Supplier;
use App\Domains\Products\Models\Unit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    return view('backend.products.supplier.index');
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    return view('backend.products.supplier.create');
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
    Supplier::create($data);
    return redirect()
      ->route('admin.product.supplier.index')
      ->withFlashSuccess('supplier created successfully');
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
    $supplier = Supplier::findOrFail($id);
    return view('backend.products.supplier.edit', compact('supplier'));
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
    $supplier = Supplier::find($id);
    if ($supplier) {
      $supplier->update($data);
    }
    return redirect()
      ->route('admin.product.supplier.index')
      ->withFlashSuccess('supplier updated successfully');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $supplier = Supplier::withTrashed()->find($id);
    if ($supplier->trashed()) {
      $supplier->forceDelete();
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Supplier permanently deleted',
      ]);
    } else if ($supplier->delete()) {
      return \response([
        'status' => true,
        'icon' => 'success',
        'msg' => 'Supplier moved to trashed successfully',
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
      'name' => 'required|string|max:191',
      'contact_person' => 'required|string|max:191',
      'phone' => 'required|string|max:191|unique:suppliers,phone,' . $id,
      'email' => 'required|email|max:191|unique:suppliers,phone,' . $id,
      'address' => 'required|string|max:400',
      'description' => 'nullable|string|max:800',
      'previous_due' => 'nullable',
    ]);
    $data['active'] = request('active', null);
    if (!$id) {
      $data['user_id'] = auth()->id();
    }
    return $data;
  }



  public function trash()
  {
    return view('backend.products.supplier.trash');
  }

  public function restore($id)
  {
    Supplier::onlyTrashed()->findOrFail($id)->restore();
    return redirect()->route('admin.product.supplier.index')
      ->withFlashSuccess('Supplier Recovered Successfully');
  }
}
