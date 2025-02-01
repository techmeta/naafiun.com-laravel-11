<?php

namespace App\Domains\Settings\Http\Controllers;

use App\Domains\Settings\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.setting.frontend.banner.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.setting.frontend.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateBanner();
        Banner::create($data);
        return redirect()
            ->route('admin.setting.frontend.banner.index')
            ->withFlashSuccess('Banner created successfully');
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
        $banner = Banner::findOrFail($id);
        return view('backend.setting.frontend.banner.edit', compact('banner'));
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
        $data = $this->validateBanner($id);
        $banner = Banner::findOrFail($id);

        $banner->update($data);

        return redirect()
            ->route('admin.setting.frontend.banner.index')
            ->withFlashSuccess('Banner Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner = Banner::withTrashed()->find($id);
        if ($banner->trashed()) {
            $banner->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Banner permanently deleted',
            ]);
        } else if ($banner->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Banner moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }



    public function validateBanner($id = 0)
    {
        $data = request()->validate([
            'active' => 'nullable|string|max:191',
            'title' => 'required|nullable|string|max:191',
            'content' => 'nullable|string|max:255',
            'banner_url' => 'nullable|string|max:255',
            'banner_image' => 'nullable|max:1800|mimes:jpeg,jpg,png,gif,webp',
        ]);

        unset($data['banner_image']);
        $data['active'] = request('active', null);

        if (!$id) {
            $data['user_id'] = auth()->id();
        }

        if (request()->hasFile('banner_image')) {
            $logo = request()->file('banner_image');
            $data['banner_image'] = store_picture($logo, 'banners');
        }

        return $data;
    }
}
