<?php

namespace App\Domains\Products\Models\Book;

//use App\Domains\Auth\Models\User;
use App\Domains\Products\Models\RecentView;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Products\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Laravel\Scout\Searchable;

class Book extends Model
{
    use  HasFactory, SoftDeletes;

    protected $table = 'boi_books';

    public $primaryKey = 'id';

    public $timestamps = true;

    protected $guarded = [];

    public function searchableAs(): string
    {
        return 'naafiun_books_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'name_bn' => $this->name_bn,
            'url_key' => $this->url_key,
            'book_cover_image' => $this->book_cover_image ? asset($this->book_cover_image) : null,
            'writers' => $this->writers->map(function ($writer) {
                return [
                    'name' => $writer->name,
                    'slug' => $writer->slug,
                ];
            }),
            'translators' => $this->translators->map(function ($translator) {
                return [
                    'name' => $translator->name,
                    'slug' => $translator->slug,
                ];
            }),
            'publishers' => $this->publishers->map(function ($publisher) {
                return [
                    'name' => $publisher->name,
                    'slug' => $publisher->slug,
                ];
            }),
            'editors' => $this->editors->map(function ($editor) {
                return [
                    'name' => $editor->name,
                    'slug' => $editor->slug,
                ];
            }),
            'subjects' => $this->subjects->map(function ($subject) {
                return [
                    'name' => $subject->name,
                    'slug' => $subject->slug,
                ];
            }),
            'transcriptions' => $this->transcriptions->map(function ($transcription) {
                return [
                    'name' => $transcription->name,
                    'slug' => $transcription->slug,
                ];
            }),
            'taxonomies' => $this->taxonomies->map(function ($taxonomy) {
                return [
                    'name' => $taxonomy->name,
                    'slug' => $taxonomy->slug,
                ];
            }),
            // Add other searchable attributes
        ];

        // php artisan scout:import "App\Domains\Products\Models\Book\Book"
    }

    public function taxonomies(): BelongsToMany
    {
        return $this->belongsToMany(Taxonomy::class, 'boi_book_taxonomy', 'book_id', 'taxonomy_id');
    }

    public function writers(): BelongsToMany
    {
        return $this->belongsToMany(BookWriter::class, 'boi_book_writers', 'book_id', 'writer_id');
    }

    public function translators(): BelongsToMany
    {
        return $this->belongsToMany(BookTranslator::class, 'boi_book_translators', 'book_id', 'translator_id');
    }

    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(BookPublisher::class, 'boi_book_publisher', 'book_id', 'publisher_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(BookSubject::class, 'boi_book_subjects', 'book_id', 'subject_id');
    }

    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(BookEditor::class, 'boi_book_editor', 'book_id', 'editor_id');
    }

    public function transcriptions(): BelongsToMany
    {
        return $this->belongsToMany(BookTranscription::class, 'boi_book_transcription', 'book_id', 'transcription_id');
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id', 'id');
    }

    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id', 'id');
    }

//    public function user(): BelongsTo
//    {
//        return $this->belongsTo(User::class);
//    }

    public function recentItems(): HasMany
    {
        return $this->hasMany(RecentView::class, 'product_id', 'id');
    }
}
