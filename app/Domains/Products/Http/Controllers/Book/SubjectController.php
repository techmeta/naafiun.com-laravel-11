<?php

namespace App\Domains\Products\Http\Controllers\Book;

use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Models\Book\BookSubject;
use App\Domains\Products\Traits\BookTrait;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    use BookTrait, ProductsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.products.book.subject.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.products.book.subject.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateSubject();
        // dd($data);
        BookSubject::create($data);

        return redirect()
            ->route('admin.product.book.subject.index')
            ->withFlashSuccess('Subject Created successfully');
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
        $subject = BookSubject::findOrFail($id);
        return view('backend.products.book.subject.edit', compact('subject'));
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
        $data = $this->validateSubject($id);
        $subject = BookSubject::find($id);
        if ($subject) {
            $subject->update($data);
        }

        return redirect()
            ->route('admin.product.book.subject.index')
            ->withFlashSuccess('Subject updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subject = BookSubject::withTrashed()->find($id);
        if ($subject->trashed()) {
            $subject->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Subject permanently deleted',
            ]);
        } else if ($subject->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Subject moved to trashed successfully',
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


    public function validateSubject($id = 0)
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
