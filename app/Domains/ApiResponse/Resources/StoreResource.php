<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): StoreResource
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

        if (hasArrayKeyOrData('simple', $relations)) {
            $data = [
                "uuid" => $this->uuid,
                "name" => $this->name,
                "slug" => $this->slug,
                "logo" => $this->logo ? asset($this->logo) : '',
                "description" => $this->description,
                "meta_title" => $this->meta_title,
                "meta_description" => $this->meta_description,
            ];
        } else {
            $data = [
                "id" => $this->id,
                "active" => $this->active,
                "uuid" => $this->uuid,
                "name" => $this->name,
                "slug" => $this->slug,
                "logo" => $this->logo ? asset($this->logo) : '',
                "description" => $this->description,
                "address" => $this->address,
                "phone" => $this->phone,
                "email" => $this->email,
                "website" => $this->website,
                "created_at" => $this->created_at,
                "meta_title" => $this->meta_title,
                "meta_description" => $this->meta_description,
            ];
        }

        if (hasArrayKeyOrData('user', $relations)) {
            $data['user'] = $this->user;
        }

        if (hasArrayKeyOrData('products', $relations)) {
            $data['products'] = BookResource::collection($relations['products'], ['simple'])->response()->getData(true);
        }

        return $data;
    }

}
