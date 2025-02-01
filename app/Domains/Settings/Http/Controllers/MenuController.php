<?php

namespace App\Domains\Settings\Http\Controllers;

use App\Domains\Settings\Models\Banner;
use App\Domains\Settings\Models\Block;
use App\Domains\Settings\Models\Menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{

    public function __construct(Menu $menu)
    {
        \Cache::forget('all_menus');
        $menus =  $menu->whereNotNull('active')->get();
        \Cache::put('all_menus', $menus, now()->addDays(180));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.setting.frontend.menu.index');
    }

    public function menuLocations()
    {
        return [
            'top_main_menu' => 'Top Main Menu',
            'footer_first_column_menu' => 'Footer First Column Menu',
            'footer_secound_column_menu' => 'Footer Second Column Menu',
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $locations = $this->menuLocations();
        return view('backend.setting.frontend.menu.create', compact('locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateMenu();
        Menu::create($data);
        return redirect()
            ->route('admin.setting.frontend.menu.index')
            ->withFlashSuccess('Menu created successfully');
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
        $menu = Menu::findOrFail($id);
        $locations = $this->menuLocations();
        return view('backend.setting.frontend.menu.edit', compact('menu', 'locations'));
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
        $data = $this->validateMenu($id);
        Menu::find($id)->update($data);

        return redirect()
            ->route('admin.setting.frontend.menu.index')
            ->withFlashSuccess('Menu Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $block = Menu::find($id);
        if ($block->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Menu moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }


    public function validateMenu($id = 0)
    {
        $data = request()->validate([
            'active' => 'nullable|string|max:191',
            'menu_name' => 'required|string|max:255',
            'menu_url' => 'required|string|max:255',
            'menu_location' => 'required|string|max:255'
        ]);

        $data['active'] = request('active', null);
        $data['new_tab'] = request('new_tab', null);

        if (!$id) {
            $data['user_id'] = auth()->id();
        }


        return $data;
    }
}
