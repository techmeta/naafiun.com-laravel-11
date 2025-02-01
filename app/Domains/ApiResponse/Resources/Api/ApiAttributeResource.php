<?php

namespace App\Domains\ApiResponse\Resources\Api;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiAttributeResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): ApiAttributeResource
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
        $relations = self::$relations;

        $data = [
            "id" => $this->id,
            "active" => $this->active,
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            "created_at" => $this->created_at,
        ];


        if (in_array('attributeItems', $relations)) {
            $data['attribute_items'] = ApiAttributeResource::collection($this->attributeItems);
        }

        if (in_array('attribute_items_count', $relations)) {
            $data['attribute_items_count'] = $this->attribute_items_count;
        }

        if (in_array('user', $relations)) {
            $data['user'] = $this->user ? [
                'name' => $this->user->name,
            ] : null;
        }


        return $data;
    }

}
