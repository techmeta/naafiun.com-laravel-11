<?php

namespace App\Domains\Products\Http\Controllers\Book;

use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Products\Models\Supplier;
use App\Domains\Products\Models\Unit;
use App\Domains\Products\Traits\BookTrait;
use App\Domains\Products\Traits\ProductsTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReportableException;
use Illuminate\Support\Facades\Log;
use Exception;

class BookController extends Controller
{

    use BookTrait, ProductsTrait;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('backend.products.book.inhouse.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $categories = Taxonomy::whereNotNull('active')
            ->whereNull('parent_id')
            ->whereNull('type')
            ->pluck('name', 'id');
        $units = Unit::whereNotNull('active')->pluck('name', 'id');
        $suppliers = Supplier::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
        $genSku = $this->generateBookCoreSku();
        return view('backend.products.book.inhouse.create', compact('categories', 'suppliers', 'units', 'genSku'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $this->validateBook();
        DB::beginTransaction();
        try {
            $taxonomy = $this->mergedTaxonomies();
            $writers = $this->getAllWriterIds();
            $translators = $this->getAllTranslators();
            $publishers = $this->getAllPublishers();
            $subjects = $this->getAllSubjects();
            $editors = $this->getAllEditors();
            $transcriptions = $this->getAllTranscriptions();
            $book = Book::create($data);
            $book->taxonomies()->sync($taxonomy);
            $book->writers()->sync($writers);
            $book->translators()->sync($translators);
            $book->publishers()->sync($publishers);
            $book->subjects()->sync($subjects);
            $book->editors()->sync($editors);
            $book->transcriptions()->sync($transcriptions);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getFile() . '::' . $exception->getLine() . ' :: ' . $exception->getMessage());
            throw new ReportableException(__('There was a problem creating this book. Please try again.'));
        }
        DB::commit();

        return redirect()
            ->route('admin.product.book.inhouse.index')
            ->withFlashSuccess('Book Created successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $book = Book::with('writers', 'translators', 'publishers', 'subjects', 'editors', 'transcriptions', 'saleUnit', 'purchaseUnit')->findOrFail($id);
        $taxonomies = Taxonomy::whereNotNull('active')->get();
        $units = Unit::whereNotNull('active')->pluck('name', 'id');
        $suppliers = Supplier::whereNotNull('active')->pluck('name', 'id')->prepend(' - Select - ', '');
        $genSku = $this->generateBookCoreSku();
        return view('backend.products.book.inhouse.edit', compact('book', 'taxonomies', 'suppliers', 'units'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $book = Book::findOrFail($id);
            $data = $this->validateBook($id);
            $taxonomy = $this->mergedTaxonomies();
            $writers = $this->getAllWriterIds();
            $translators = $this->getAllTranslators();
            $publishers = $this->getAllPublishers();
            $subjects = $this->getAllSubjects();
            $editors = $this->getAllEditors();
            $transcriptions = $this->getAllTranscriptions();
            $book->fill($data);
            $book->save();
            $book->taxonomies()->sync($taxonomy);
            $book->writers()->sync($writers);
            $book->translators()->sync($translators);
            $book->publishers()->sync($publishers);
            $book->subjects()->sync($subjects);
            $book->editors()->sync($editors);
            $book->transcriptions()->sync($transcriptions);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getFile() . '::' . $exception->getLine() . ' :: ' . $exception->getMessage());
            throw new ReportableException(__('There was a problem creating this book. Please try again.'));
        }
        DB::commit();

        return redirect()
            ->route('admin.product.book.inhouse.index')
            ->withFlashSuccess('Book updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $book = Book::withTrashed()->find($id);
        if ($book->trashed()) {
            $book->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Book permanently deleted',
            ]);
        } else if ($book->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Book moved to trashed successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }


    public function restore($id)
    {
        Book::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.product.book.inhouse.index')
            ->withFlashSuccess('Book Recovered Successfully');
    }


    public function ajaxImageUpload()
    {
        $data['location'] = '';
        if (request()->hasFile('file')) {
            $file = request()->file('file');
            $name = 'upload-' . time();
            $data['location'] = '/' . store_picture($file, 'editor', $name);
        }
        return response($data);
    }
}
