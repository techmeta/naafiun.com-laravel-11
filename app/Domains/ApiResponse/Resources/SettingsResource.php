<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): SettingsResource
    {
        self::$relations = $data;
        return parent::make($resource);
    }

    public static function collection($resource, $data = []): AnonymousResourceCollection
    {
        self::$relations = $data;
        return parent::collection($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $assets = $this->assetsProperties();
        if (in_array($this->key, $assets)) {
            return [
                "key" => $this->key,
                "value" => $this->value ? asset($this->value) : null,
            ];
        }

        $jsons = $this->jsonProperties();
        if (in_array($this->key, $jsons)) {
            return [
                "key" => $this->key,
                "value" => json_decode($this->value, true),
            ];
        }

        return [
            "key" => $this->key,
            "value" => $this->value,
        ];
    }


    public function assetsProperties(): array
    {
        return [
            'meta_image',
            'admin_logo',
            'favicon',
            'frontend_logo_menu',
            'frontend_logo_footer',
            'right_banner_mobile',
            'right_banner_image',
            'right_banner_image_sm'
        ];
    }

    public function jsonProperties(): array
    {
        return [
            'frontend_under_banner_two',
            'frontend_under_banner_three',
        ];
    }

}
