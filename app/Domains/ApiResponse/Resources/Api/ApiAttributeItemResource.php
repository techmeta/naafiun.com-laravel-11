<?php

namespace App\Domains\ApiResponse\Resources\Api;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiAttributeItemResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): ApiAttributeItemResource
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
            "label" => $this->label,
            "value" => $this->value,
            "created_at" => $this->created_at,
        ];

        if (in_array('attribute', $relations)) {
            $data['attribute'] = $this->attribute ? ApiAttributeResource::single($this->attribute) : null;
        }

        if (in_array('user', $relations)) {
            $data['user'] = $this->user ? [
                'name' => $this->user->name,
            ] : null;
        }


        return $data;
    }

}
