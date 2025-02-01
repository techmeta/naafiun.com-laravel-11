<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Domains\Products\Models\Book{
/**
 * 
 *
 * @property int $id
 * @property string $type
 * @property string|null $name
 * @property string|null $name_bn
 * @property string|null $url_key
 * @property string|null $book_cover
 * @property string|null $version
 * @property string|null $book_isbn
 * @property string|null $book_preview
 * @property string|null $total_page
 * @property string|null $language
 * @property float|null $sale_price
 * @property float|null $discount_price
 * @property float|null $purchase_price
 * @property int|null $opening_stock
 * @property int|null $supplier_id
 * @property int|null $purchase_unit_id
 * @property int|null $sale_unit_id
 * @property int|null $conversion_rate
 * @property int|null $alert_qty
 * @property string|null $is_new
 * @property string|null $available
 * @property int|null $order_limit
 * @property string|null $book_cover_image
 * @property string|null $gallery
 * @property string|null $video_provider
 * @property string|null $video_link
 * @property string|null $tags
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $discount_type
 * @property float|null $tax
 * @property string|null $tax_type
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_img
 * @property string|null $pdf
 * @property string|null $barcode
 * @property string|null $more_books_priority more_books_priority_ids
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookEditor> $editors
 * @property-read int|null $editors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookPublisher> $publishers
 * @property-read int|null $publishers_count
 * @property-read Unit|null $purchaseUnit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RecentView> $recentItems
 * @property-read int|null $recent_items_count
 * @property-read Unit|null $saleUnit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookSubject> $subjects
 * @property-read int|null $subjects_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies
 * @property-read int|null $taxonomies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookTranscription> $transcriptions
 * @property-read int|null $transcriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookTranslator> $translators
 * @property-read int|null $translators_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domains\Products\Models\Book\BookWriter> $writers
 * @property-read int|null $writers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Book newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Book newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Book onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Book query()
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereAlertQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereBookCover($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereBookCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereBookIsbn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereBookPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereConversionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereDiscountPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereGallery($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereMetaImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereMoreBooksPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereOpeningStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereOrderLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book wherePdf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book wherePurchasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereSaleUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereTaxType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereTotalPage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereUrlKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereVideoLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book whereVideoProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Book withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Book withoutTrashed()
 * @mixin \Eloquent
 */
	class Book extends \Eloquent {}
}

