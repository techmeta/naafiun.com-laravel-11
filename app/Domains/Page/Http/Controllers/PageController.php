<?php

namespace App\Domains\Page\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\Page\Models\Page;


class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.page.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.page.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validatePage();
        Page::create($data);
        return redirect()
            ->route('admin.page.index')
            ->withFlashSuccess('page created successfully');
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
        $page = Page::findOrFail($id);
        return view('backend.page.edit', compact('page'));
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
        $data = $this->validatePage($id);
        $page = Page::find($id);
        if ($page) {
            $page->update($data);
        }
        return redirect()
            ->route('admin.page.index')
            ->withFlashSuccess('Page updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Page::withTrashed()->find($id);
        if ($page->trashed()) {
            $page->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Page permanently deleted',
            ]);
        } else if ($page->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Page moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }


    public function validatePage($id = 0)
    {
        $data = request()->validate([
            'title' => 'required|nullable|string|max:191',
            'slug' => 'required|string|max:191',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:400',
            'thumb' => 'nullable|max:6600|mimes:jpeg,jpg,png,gif,webp',
            'status' => 'nullable',
        ]);

        if (!$id) {
            $data['user_id'] = auth()->id();
        }

        if (request()->hasFile('thumb')) {
            $thumb = request()->file('thumb');
            $data['thumb'] = store_picture($thumb, 'thumb');
        }

        return $data;
    }
}
