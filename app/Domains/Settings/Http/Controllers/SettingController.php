<?php

namespace App\Domains\Settings\Http\Controllers;

use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function generalSetting()
    {
        return view('backend.setting.frontend.general.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function generalSettingStore(Request $request)
    {
        $data = request()->all();
        unset($data['_token']);

        if (\request()->hasFile('meta_image')) {
            $data['meta_image'] = store_picture(\request()->file('meta_image'), 'setting/meta');
        }

        Setting::save_settings($data);
        Cache::forget('settings'); // remove setting cache

        return redirect()->back()->withFlashSuccess('Setting Updated Successfully');
    }

}
