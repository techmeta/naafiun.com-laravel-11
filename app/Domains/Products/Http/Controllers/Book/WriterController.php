<?php

namespace App\Domains\Products\Http\Controllers\Book;

use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Models\Book\BookWriter;
use App\Domains\Products\Traits\BookTrait;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WriterController extends Controller
{

    use BookTrait, ProductsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.products.book.writer.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.products.book.writer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateWriter();
        // dd($data);
        BookWriter::create($data);

        return redirect()
            ->route('admin.product.book.writer.index')
            ->withFlashSuccess('Writer Created successfully');
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
        $writer = BookWriter::findOrFail($id);
        return view('backend.products.book.writer.edit', compact('writer'));
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
        $data = $this->validateWriter($id);
        $writer = BookWriter::find($id);
        if ($writer) {
            $writer->update($data);
        }

        return redirect()
            ->route('admin.product.book.writer.index')
            ->withFlashSuccess('Writer updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $writer = BookWriter::withTrashed()->find($id);
        if ($writer->trashed()) {
            $writer->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Writer permanently deleted',
            ]);
        } else if ($writer->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Writer moved to trashed successfully',
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


    public function validateWriter($id = 0)
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
