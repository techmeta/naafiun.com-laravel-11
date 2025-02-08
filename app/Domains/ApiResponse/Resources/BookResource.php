<?php

namespace App\Domains\ApiResponse\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookResource extends JsonResource
{
    protected static array $relations = [];

    public static function single($resource, $data = []): BookResource
    {
        self::$relations = $data;
        return parent::make($resource);
    }

    public static function collection($resource, $data = []): AnonymousResourceCollection
    {
        self::$relations = $data;
        return parent::collection($resource);
    }


    public function toArray($request): array|\JsonSerializable|Arrayable
    {
        $relations = self::$relations;

        $sale_price = $this->sale_price;
        $discount_price = $this->discount_price;
        $book_cover = $this->book_cover_image ? asset($this->book_cover_image) : '';

        // Calculate the discount rate
        $discount_rate = (($sale_price - $discount_price) / $sale_price) * 100;
        $discount_rate = round($discount_rate);


        if (hasArrayKeyOrData('simple', $relations)) {
            $simpleData = [
                "id" => $this->id,
                "name" => $this->name,
                "name_bn" => $this->name_bn,
                "slug" => $this->url_key,
                "book_cover" => $this->book_cover,
                "version" => $this->version,
                "total_page" => $this->total_page,
                "sale_price" => $sale_price,
                "discount_price" => $discount_price,
                "discount_rate" => $discount_rate,
                "alert_qty" => $this->alert_qty,
                "available" => $this->available,
                "order_limit" => $this->order_limit,
                "short_description" => $this->short_description,
                "thumbnail_img" => $book_cover,
                "book_cover_image" => $book_cover,
                "rating" => rand(4, 5),
            ];

            if (hasArrayKeyOrData('writers', $relations)) {
                $simpleData['writers'] = WriterResource::collection($this->writers);
            }


            return $simpleData;
        }

        $data = [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "name" => $this->name,
            "name_bn" => $this->name_bn,
            "url_key" => $this->url_key,
            "book_cover" => $this->book_cover,
            "version" => $this->version,
            "book_isbn" => $this->book_isbn,
            "book_preview" => $this->book_preview,
            "total_page" => $this->total_page,
            "language" => $this->language,
            "sale_price" => $sale_price,
            "discount_price" => $discount_price,
            "discount_rate" => $discount_rate,
            "conversion_rate" => $this->conversion_rate,
            "alert_qty" => $this->alert_qty,
            "is_new" => $this->is_new,
            "available" => $this->available,
            "order_limit" => $this->order_limit,
            "thumbnail_img" => $book_cover,
            "book_cover_image" => $book_cover,
            "video_provider" => $this->video_provider,
            "video_link" => $this->video_link,
            "short_description" => $this->short_description,
            "description" => $this->description,
            "discount_type" => $this->discount_type,
            "tax" => $this->tax,
            "tax_type" => $this->tax_type,
            "meta_img" => $this->meta_img,
            "pdf" => $this->pdf,
            "more_books_priority" => $this->more_books_priority,

//            "last30_days_sale" => $this->order_items_count,
            "barcode" => $this->barcode,
            "tags" => $this->tags,
            "meta_title" => $this->name ? Str::limit($this->name, 60) : null,
            "meta_description" => $this->description ? Str::limit($this->description, 120) : null,
        ];
        $pictures = [];
        if (isset($this->gallery)) {
            $pictures = json_decode($this->gallery) ?? [];
            $pictures = collect($pictures)->map(function ($item) {
                $data = [];
                $data['status'] = 'done';
                $data['url'] = $item->large_url ? asset($item->large_url) : null;
                $data['large_url'] = $item->large_url ? asset($item->large_url) : null;
                $data['small_url'] = $item->small_url ? asset($item->small_url) : null;
                return $data;
            });
        }

        $data['pictures'] = count($pictures) > 0 ? $pictures : [['url' => $book_cover, 'large_url' => $book_cover]];

        if (hasArrayKeyOrData('writers', $relations)) {
            $data['writers'] = WriterResource::collection($this->writers);
        }
        if (hasArrayKeyOrData('translators', $relations)) {
            $data['translators'] = $this->translators->map(function ($data) {
                unset($data['pivot']);
                $data['url'] = $data->slug ? "/book?publisher={$data->slug}" : "/book";
                return $data;
            });;
        }
        if (hasArrayKeyOrData('publishers', $relations)) {
            $data['publishers'] = $this->publishers->map(function ($data) {
                unset($data['pivot']);
                $data['url'] = $data->slug ? "/book?publisher={$data->slug}" : "/book";
                return $data;
            });
        }
        if (hasArrayKeyOrData('subjects', $relations)) {
            $data['subjects'] = $this->subjects->map(function ($data) {
                unset($data['pivot']);
                $data['url'] = $data->slug ? "/book?subject={$data->slug}" : "/book";
                return $data;
            });;
        }
        if (hasArrayKeyOrData('editors', $relations)) {
            $data['editors'] = $this->editors->map(function ($data) {
                unset($data['pivot']);
                $data['url'] = $data->slug ? "/book?editor={$data->slug}" : "/book";
                return $data;
            });;
        }
        if (hasArrayKeyOrData('transcriptions', $relations)) {
            unset($data['pivot']);
            $data['transcriptions'] = $this->transcriptions->map(function ($data) {
                $data['url'] = $data->slug ? "/book?transcription={$data->slug}" : "/book";
                return $data;
            });;
        }
        if (hasArrayKeyOrData('currencyIcon', $relations)) {
            $data['currency'] = $relations['currencyIcon'];
        }

        return $data;
    }

}
