<?php

namespace App\Domains\Products\Traits;

use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Book\BookEditor;
use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Models\Book\BookSubject;
use App\Domains\Products\Models\Book\BookTranscription;
use App\Domains\Products\Models\Book\BookTranslator;
use App\Domains\Products\Models\Book\BookWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait BookTrait
{

    public function generateBookCoreSku()
    {
        $book = Book::latest()->first();
        $nextBookId = $book ? ($book->id + 1) : 1;
        return 'naaf-' . generate_zero_prefix_number($nextBookId, 7);
    }

    public function validateBook($id = 0)
    {

        $req = request()->validate([
            'name' => 'nullable|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'url_key' => 'required|string|max:191|unique:boi_books,url_key,' . $id,
            'categories' => 'required|array|exists:taxonomies,id',
            'subcategories' => 'nullable|array|exists:taxonomies,id',
            'total_page' => 'nullable|numeric',
            'book_cover' => 'nullable|string|max:191',
            'version' => 'nullable|string|max:191',
            'book_isbn' => 'nullable|string|max:191',
            'language' => 'nullable|string|max:255',

            'sale_price' => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'opening_stock' => 'nullable|numeric',
            'supplier_id' => 'nullable|exists:suppliers,id',

            'purchase_unit_id' => 'required|exists:units,id',
            'sale_unit_id' => 'required|exists:units,id',
            'conversion_rate' => 'required|numeric',
            'alert_qty' => 'required|numeric',
            'available' => 'required|string|max:55',
            'order_limit' => 'required|numeric',

            'book_cover_image' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',
            'gallery_img_one' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',
            'gallery_img_two' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',
            'gallery_img_three' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',
            'gallery_img_four' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',

            'video_provider' => 'nullable|string|max:191',
            'video_link' => 'nullable|string|max:191',

            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:400',
            'meta_img' => 'nullable|max:9000|mimes:jpeg,jpg,png,gif,webp',

            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'book_preview' => 'nullable|string',

            'more_books_priority' => 'nullable|string|max:1800',

        ]);
        unset($req['thumbnail_img'], $req['categories'], $req['subcategories'], $req['book_cover_image'], $req['gallery_img_one'], $req['gallery_img_two'], $req['gallery_img_three'], $req['gallery_img_four'], $req['meta_img']);


        $req['supplier_id'] = request('supplier_id', null);

        if (!$id) {
            $req['user_id'] = auth()->id();
        }

        $image_source = request('image_source');
        if ($image_source == 'external') {
            $source = request('external_image_url');
            if ($source) {
                $imageContent = file_get_contents($source);
                if ($imageContent !== false) {
                    $extension = explode('.', $source);
                    $extension = end($extension);
                    $path = 'book/' . time() . '.' . $extension;
                    Storage::disk('public')->put($path, $imageContent);
                    $req['book_cover_image'] = 'storage/' . $path;
                }
            }
        } else if ($image_source == 'internal') {
            if (request()->hasFile('book_cover_image')) {
                $thumbnail_img = request()->file('book_cover_image');
                $name = 'thumb-' . time();
                $req['book_cover_image'] = store_picture($thumbnail_img, 'book', $name);
            }
        }


        $galleryItem = [];
        if (request()->hasFile('gallery_img_one')) {
            $flash_deal_img = request()->file('gallery_img_one');
            $name = 'galleryOne-' . time();
            $galleryItem['gallery_img_one'] = store_picture($flash_deal_img, 'gallery', $name);
        }
        if (request()->hasFile('gallery_img_two')) {
            $flash_deal_img = request()->file('gallery_img_two');
            $name = 'galleryTwo-' . time();
            $galleryItem['gallery_img_two'] = store_picture($flash_deal_img, 'gallery', $name);
        }

        if (request()->hasFile('gallery_img_three')) {
            $flash_deal_img = request()->file('gallery_img_three');
            $name = 'galleryThree-' . time();
            $galleryItem['gallery_img_three'] = store_picture($flash_deal_img, 'gallery', $name);
        }

        if (request()->hasFile('gallery_img_four')) {
            $flash_deal_img = request()->file('gallery_img_four');
            $name = 'galleryFour-' . time();
            $galleryItem['gallery_img_four'] = store_picture($flash_deal_img, 'gallery', $name);
        }

        if (!empty($galleryItem)) {
            $req['gallery'] = json_encode($galleryItem);
        }

        return $req;
    }


    public function getAllWriterIds()
    {
        $writers = request('writer', []);
        $writerIds = [];
        if (empty($writers)) {
            return $writerIds;
        }
        foreach ($writers as $writer) {
            if ($writer) {
                $newWriter = BookWriter::where('name', $writer)->first();
                if (!$newWriter) {
                    $newWriter = new BookWriter();
                }
                $newWriter->name = $writer;
                $newWriter->slug = Str::slug($writer);
                $newWriter->user_id = auth()->id();
                $newWriter->save();
                array_push($writerIds, $newWriter->id);
            }
        }
        return $writerIds;
    }

    public function getAllTranslators()
    {
        $translators = request('translator', []);
        $translatorIds = [];
        foreach ($translators as $translator) {
            if (!empty($translator)) {
                $newTranslator = BookTranslator::firstOrCreate(
                    ['name' => $translator],
                    ['active' => now(), 'slug' => Str::slug($translator), 'user_id' => auth()->id()]
                );
                if ($newTranslator) {
                    array_push($translatorIds, $newTranslator->id);
                }
            }
        }
        return $translatorIds;
    }

    public function getAllPublishers()
    {
        $publishers = request('publisher', []);
        $publisherIds = [];
        foreach ($publishers as $publisher) {
            if (!empty($publisher)) {
                $newPublisher = BookPublisher::firstOrCreate(
                    ['name' => $publisher],
                    ['active' => now(), 'slug' => Str::slug($publisher), 'user_id' => auth()->id()]
                );
                if ($newPublisher) {
                    array_push($publisherIds, $newPublisher->id);
                }
            }
        }
        return $publisherIds;
    }

    public function getAllSubjects()
    {
        $subjects = request('subject', []);
        $subjectIds = [];
        foreach ($subjects as $subject) {
            if (!empty($subject)) {
                $newSubject = BookSubject::firstOrCreate(
                    ['name' => $subject],
                    ['active' => now(), 'slug' => Str::slug($subject), 'user_id' => auth()->id()]
                );
                if ($newSubject) {
                    array_push($subjectIds, $newSubject->id);
                }
            }
        }
        return $subjectIds;
    }

    public function getAllEditors()
    {
        $editors = request('editor', []);
        $editorIds = [];
        foreach ($editors as $editor) {
            if (!empty($editor)) {
                $newEditor = BookEditor::firstOrCreate(
                    ['name' => $editor],
                    ['active' => now(), 'slug' => Str::slug($editor), 'user_id' => auth()->id()]
                );
                if ($newEditor) {
                    array_push($editorIds, $newEditor->id);
                }
            }
        }
        return $editorIds;
    }

    public function getAllTranscriptions()
    {
        $transcriptions = request('transcription', []);
        $transcriptionIds = [];
        foreach ($transcriptions as $transcription) {
            if (!empty($transcription)) {
                $newTranscription = BookTranscription::firstOrCreate(
                    ['name' => $transcription],
                    ['active' => now(), 'slug' => Str::slug($transcription), 'user_id' => auth()->id()]
                );
                if ($newTranscription) {
                    array_push($transcriptionIds, $newTranscription->id);
                }
            }
        }
        return $transcriptionIds;
    }
}
