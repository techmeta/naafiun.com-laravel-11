<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\ApiResponse\Resources\SettingsResource;
use App\Domains\Page\Models\Page;
use App\Domains\Page\Models\Post;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Settings\Models\Banner;
use App\Domains\Settings\Models\Setting;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Class GeneralService.
 */
class GeneralService
{

    public function getUserFromToken($token)
    {
        // Find the token in the database
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if ($personalAccessToken) {
            // Retrieve the user associated with the token
            return $personalAccessToken->tokenable; // This will return the user model
        }

        return null; // Token not found
    }

    public function allSettings(): array
    {
        $settings = Setting::query()->whereNotNull('active')->get();
        $data1 = SettingsResource::collection($settings);
        return collect($data1)->pluck('value', 'key')->toArray();
    }

    public function all_menus()
    {
        return Taxonomy::query()
            ->whereNotNull('active')
            ->where('top', 1)
            ->select('id', 'name', 'slug', 'icon', 'featured', 'top', 'type', 'parent_id', 'order', 'options', 'menu_type', 'url')
            ->withCount('children')
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['icon'] = $item->icon ? asset($item->icon) : '';
                return $data;
            });
    }

    public function allCategories()
    {
        return Taxonomy::query()
            ->whereNotNull('active')
            ->select('id', 'name', 'slug', 'icon', 'featured', 'top', 'type', 'parent_id', 'order', 'options', 'menu_type', 'url')
            ->withCount('children')
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['icon'] = $item->icon ? asset($item->icon) : '';
                return $data;
            });
    }

    public function top_categories()
    {
        $limit = request('limit', 8);
        return Taxonomy::query()
            ->whereNotNull('active')
            ->whereNotNull('top')
            ->select('id', 'name', 'slug', 'icon', 'featured', 'top', 'type', 'parent_id', 'order', 'options', 'menu_type', 'url')
            ->orderBy('order')
            ->offset(0)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['icon'] = $item->icon ? asset($item->icon) : '';
                return $data;
            });
    }

    public function allBanners()
    {
        return Banner::query()
            ->whereNotNull('active')
            ->limit(10)
            ->latest()
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['post_title'] = $item->title ?: null;
                $data['post_thumb'] = $item->banner_image ? asset($item->banner_image) : null;
                return $data;
            });
    }

    public function faqPages()
    {
        return Post::where('post_status', 'publish')
            ->where('post_type', 'faq')
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['post_title'] = $item->title ?: null;
                $data['post_thumb'] = $item->post_thumb ? asset($item->post_thumb) : null;
                return $data;
            });
    }

    public function get_page($slug)
    {
        return Page::where('status', 'publish')
            ->where('slug', $slug)
            ->get()
            ->map(function ($item) {
                $data = $item;
                $data['post_title'] = $item->title ?: null;
                $data['post_thumb'] = $item->post_thumb ? asset($item->post_thumb) : null;
                return $data;
            })
            ->first();
    }
}
