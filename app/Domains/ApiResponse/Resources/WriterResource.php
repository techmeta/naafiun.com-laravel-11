<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class WriterResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): WriterResource
    {
        self::$relations = $data;
        return parent::make($resource);
    }

    public static function collection($resource, $data = []): AnonymousResourceCollection
    {
        self::$relations = $data;
        return parent::collection($resource);
    }


    public function toArray($request): array
    {
//        $relations = self::$relations;
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "top" => $this->top,
            "picture" => $this->picture ? asset($this->picture) : null,
        ];
    }

}
