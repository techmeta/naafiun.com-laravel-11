<?php

use App\Domains\Settings\Models\Block;
use App\Domains\Settings\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;


if (!function_exists('appName')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function appName(): mixed
    {
        return config('app.name', 'RayanSolve');
    }
}

if (!function_exists('carbon')) {
    /**
     * Create a new Carbon instance from a time.
     *
     * @param $time
     * @return Carbon
     *
     * @throws Exception
     */
    function carbon($time): Carbon
    {
        return new Carbon($time);
    }
}

if (!function_exists('homeRoute')) {
    /**
     * Return the route to the "home" page depending on authentication/authorization status.
     *
     * @return string
     */
    function homeRoute(): string
    {
        if (auth()->check()) {
            if (auth()->user()->isAdmin()) {
                return 'admin.dashboard';
            }

            if (auth()->user()->isUser()) {
                return 'frontend.user.dashboard';
            }
        }

        return 'frontend.index';
    }
}


if (!function_exists('store_picture')) {
    /**
     * @param $file
     * @param string $dir_path
     * @param null $name
     * @param bool $thumb
     * @param bool $resize
     * @return string
     */
    function store_picture($file, $dir_path = '/', $name = null, $thumb = false, $resize = false, $resizeWidth = 1080)
    {
        $imageName = $name ? $name . '.' . $file->getClientOriginalExtension() : $file->getClientOriginalName();
        $dir_path = 'storage/' . $dir_path;
        $pathDir = create_public_directory($dir_path); // manage directory
        $img = Image::make($file);
        $fileSize = round($img->filesize() / 1024); // convert to kb

        if ($resize) {
            $img->resize($resizeWidth, null, function ($c) {
                $c->aspectRatio();
            })->save($pathDir . '/' . $imageName, 90); // save converted photo
        } else {
            $img->save($pathDir . '/' . $imageName, 90); // save original photo
        }

        if ($thumb) {
            $thumbPathDir = create_public_directory($dir_path . '/thumbs'); // manage thumbs directory
            if ($img->width() > 400 || $fileSize > 150) {
                $img->resize(400, null, function ($c) {
                    $c->aspectRatio();
                })->save($thumbPathDir . '/' . $imageName, 90); // save thumbs photo
            } else {
                $img->save($thumbPathDir . '/' . $imageName, 90); // save thumbs photo
            }
        }

        return $dir_path . '/' . $imageName;
    }
}


if (!function_exists('create_public_directory')) {
    /**
     * @param $path
     * @return string
     */
    function create_public_directory($path): string
    {
        File::isDirectory(public_path('storage')) ?: Artisan::call('storage:link');
        File::isDirectory(public_path($path)) || File::makeDirectory(public_path($path), 0777, true, true);
        return public_path($path);
    }
}


if (!function_exists('getArrayKeyData')) {
    function getArrayKeyData($array, string $key, $default = null)
    {
        if (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        }
        return $default;
    }
}

if (!function_exists('generate_zero_prefix_number')) {
    /**
     * @param $id
     * @param int $length
     * @return string
     */
    function generate_zero_prefix_number($id, int $length = 8): string
    {
        return str_pad($id, $length, "0", STR_PAD_LEFT);
    }
}


if (!function_exists('get_all_blocks')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function get_all_blocks($json = false): mixed
    {
        $setting = Cache::get('blocks', function () {
            return Block::whereNotNull('active')->pluck('content', 'identifier')->toArray();
        });
        if ($json) {
            return json_encode($setting);
        }

        return $setting;
    }
}


if (!function_exists('get_block')) {
    /**
     * Helper to grab the application name.
     *
     * @param $identifier
     * @param null $default
     * @return mixed
     */
    function get_block($identifier, $default = null): mixed
    {
        $blocks = get_all_blocks();
        if (is_array($blocks)) {
            return array_key_exists($identifier, $blocks) ? $blocks[$identifier] : $default;
        } elseif ($blocks->isNotEmpty()) {
            $setting = $blocks->where('identifier', $identifier)->first();
            return $setting ? $setting->content : $default;
        }
        return $default;
    }
}


if (!function_exists('gravatar')) {
    /**
     * Access the gravatar helper.
     */
    function gravatar()
    {
        return app('gravatar');
    }
}

if (!function_exists('all_menus')) {
    function all_menus($key = null)
    {
        $menus = Cache::get('all_menus', collect([]));
        if ($menus->count()) {
            if ($key) {
                return $menus->where('menu_location', $key);
            }
            return $menus;
        }
        return collect([]);
    }
}

if (!function_exists('general_settings')) {
    /**
     * Helper to grab the application name.
     *
     * @param bool $json
     * @return mixed
     */
    function general_settings($json = false): mixed
    {
        $setting = Cache::get('settings');
        if (!$setting) {
            $setting = Setting::whereNotNull('active')->get();
            Cache::put('settings', $setting);
        }
        // unset($setting["currency_rate"]);
        if ($json) {
            $setting = Setting::whereNotNull('active')->pluck('value', 'key')->toArray();
            return json_encode($setting);
        }

        return $setting;
    }
}

if (!function_exists('get_setting')) {
    /**
     * Helper to grab the application name.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    function get_setting($key, $default = null): mixed
    {
        $setting = general_settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}


if (!function_exists('hasArrayKeyOrData')) {
    function hasArrayKeyOrData($key, $array): bool
    {
        return array_key_exists($key, $array) || in_array($key, $array);
    }
}


if (!function_exists('randomOTPCode')) {
    function randomOTPCode(): int
    {
        return $otpCode = rand(100000, 999999);
    }
}
