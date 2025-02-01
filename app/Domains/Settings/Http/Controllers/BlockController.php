<?php

namespace App\Domains\Settings\Http\Controllers;

use App\Domains\Settings\Models\Banner;
use App\Domains\Settings\Models\Block;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.setting.frontend.block.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.setting.frontend.block.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateBlock();
        Block::create($data);
        return redirect()
            ->route('admin.setting.frontend.block.index')
            ->withFlashSuccess('Block created successfully');
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
        $block = Block::findOrFail($id);
        return view('backend.setting.frontend.block.edit', compact('block'));
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
        $data = $this->validateBlock($id);
        Block::find($id)->update($data);

        return redirect()
            ->route('admin.setting.frontend.block.index')
            ->withFlashSuccess('Block Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $block = Block::find($id);
        if ($block->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Block moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }



    public function validateBlock($id = 0)
    {
        // id, active, name, identifier, content, user_id, created_at, updated_at
        $data = request()->validate([
            'active' => 'nullable|string|max:191',
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:191|unique:blocks,identifier,' . $id,
            'content' => 'required',
        ]);

        $data['active'] = request('active', null);
        $data['identifier'] = Str::slug($data['identifier']);

        if (!$id) {
            $data['user_id'] = auth()->id();
        }


        return $data;
    }
}
