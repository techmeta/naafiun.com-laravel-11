<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\ApiResponse\Resources\BookResource;
use App\Domains\ApiResponse\Resources\ProductResource;
use App\Domains\ApiResponse\Resources\RecentViewProductResource;
use App\Domains\ApiResponse\Resources\StoreResource;
use App\Domains\Auth\Models\User;
use App\Domains\Products\Models\Book\Book;
use App\Domains\Products\Models\Book\BookPublisher;
use App\Domains\Products\Models\Book\BookSubject;
use App\Domains\Products\Models\Book\BookWriter;
use App\Domains\Products\Models\Brand;
use App\Domains\Products\Models\Product;
use App\Domains\Products\Models\RecentViewLog;
use App\Domains\Products\Models\Taxonomy;
use App\Domains\Renewal\Models\RenewalProducts;
use App\Domains\Renewal\Models\RenewalRequest;
use App\Domains\Seller\Models\Seller;
use App\Domains\Settings\Models\Block;
use App\Domains\Training\Models\RequestDemoTraining;
use App\Domains\Training\Models\Training;
use App\Domains\Training\Models\TrainingEnroll;
use App\Domains\Training\Notifications\DemoTrainingNotification;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

/**
 * Class CatalogService.
 */
class CatalogService
{
    public function bookInstance(): Builder
    {
        return Book::with(['writers'])->where('available', 'yes');
    }

    public function section_products(): array
    {
        $section = request('section');
        $books = [];
        if ($section == 'new-books') {
            $books = $this->bookInstance()
                ->orderByDesc('is_new')
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'ebadat-and-inspiration') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->where('slug', 'atmsuddhi-oo-onuprerna');
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'iman-akeeda-bissas') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['seerate-rasuul-sa', 'akeeda']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'seerate-rasuul-sa') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['seerate-rasuul-sa']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'dua-oo-zikir') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['dua-oo-zikir']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'pribar-oo-samajik-jeebn') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['pribar-oo-samajik-jeebn']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'sunnat-oo-sishtacar') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['sunnat-oo-sishtacar']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'nbee-rasuulder-jeebnee') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['nbee-rasuulder-jeebnee']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else if ($section == 'akeeda-sunnat-oo-sishtacar') {
            $books = $this->bookInstance()
                ->whereHas('subjects', function ($query) {
                    $query->whereIn('slug', ['akeeda', 'sunnat-oo-sishtacar']);
                })
                ->orderByDesc('created_at')
                ->paginate();
        } else {
            $books = $this->bookInstance()
                ->orderByDesc('created_at')
                ->paginate();
        }
        return BookResource::collection($books, ['simple', 'writers'])->response()->getData(true);
    }

    public function filtered_products(): array
    {
        $subjects = request('subjects', []);
        $writers = request('writers', []);
        $publishers = request('publishers', []);

        $hasSubjects = $subjects ? count($subjects) : 0;
        $hasWriters = $writers ? count($writers) : 0;
        $hasPublishers = $publishers ? count($publishers) : 0;

        $books = $this->bookInstance();
        if ($hasWriters) {
            $books = $books->whereHas('writers', function ($query) use ($writers) {
                $query->whereIn('slug', $writers);
            });
        }
        if ($hasSubjects) {
            $books = $books->whereHas('subjects', function ($query) use ($subjects) {
                $query->whereIn('slug', $subjects);
            });
        }
        if ($hasPublishers) {
            $books = $books->whereHas('publishers', function ($query) use ($publishers) {
                $query->whereIn('slug', $publishers);
            });
        }

        $books = $books->orderByDesc('created_at')
            ->paginate(16);

        return BookResource::collection($books, ['simple', 'writers'])->response()->getData(true);
    }

    public function product($sku): BookResource
    {
        $startDate = now()->endOfDay()->toDateTimeString();
        $endDate = now()->subDays(30)->startOfDay()->toDateTimeString();

        $book = Book::with(['writers', 'translators', 'publishers', 'subjects', 'editors', 'transcriptions'])
            ->where('available', 'yes')
            ->where('url_key', $sku)
            ->first();

        return BookResource::single($book, ['writers', 'translators', 'publishers', 'subjects', 'editors', 'transcriptions']);
    }


    public function bookWriters($isTop = false)
    {
        $limit = request('limit', 36);
        $search = request('search');
        $data = BookWriter::whereNotNull('active');
        if ($isTop) {
            $data = $data->whereNotNull('top');
        }
        if ($search) {
            $data = $data->where('slug', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        }

        return $data->orderBy('slug') // secondary ordering by slug
            ->paginate($limit)
            ->map(function ($item) {
                $data['id'] = $item->id;
                $data['name'] = $item->name;
                $data['slug'] = $item->slug;
                $data['books_count'] = $item->books_count;
                $data['top'] = $item->top;
                $data['meta_title'] = $item->meta_title;
                $data['meta_description'] = $item->meta_description;
                $data['picture'] = $item->picture ? asset($item->picture) : null;
                return $data;
            });
    }

    public function bookPublisher($isTop = false)
    {
        $limit = request('limit', 36);
        $search = request('search');
        $data = BookPublisher::whereNotNull('active');
        if ($isTop) {
            $data = $data->whereNotNull('top');
        }
        if ($search) {
            $data = $data->where('slug', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        }
        return $data->orderBy('slug')
            ->paginate($limit)
            ->map(function ($item) {
                $data['id'] = $item->id;
                $data['name'] = $item->name;
                $data['slug'] = $item->slug;
                $data['books_count'] = $item->books_count;
                $data['top'] = $item->top;
                $data['meta_title'] = $item->meta_title;
                $data['meta_description'] = $item->meta_description;
                $data['picture'] = $item->logo ? asset($item->logo) : null;
                return $data;
            });
    }

    public function bookSubjects($isTop = false)
    {
        $limit = request('limit', 36);
        $search = request('search');
        $data = BookSubject::whereNotNull('active');
        if ($isTop) {
            $data = $data->whereNotNull('top');
        }
        if ($search) {
            $data = $data->where('slug', 'like', "%$search%")
                ->orWhere('name', 'like', "%$search%");
        }
        return $data->orderBy('slug')
            ->paginate($limit)
            ->map(function ($item) {
                $data['id'] = $item->id;
                $data['name'] = $item->name;
                $data['slug'] = $item->slug;
                $data['books_count'] = $item->books_count;
                $data['top'] = $item->top;
                $data['meta_title'] = $item->meta_title;
                $data['meta_description'] = $item->meta_description;
                $data['picture'] = $item->logo ? asset($item->logo) : null;
                return $data;
            });
    }

    public function bookAttributes($type): array
    {
        $type = $type ?: 'all';
        $items = [];
        if ($type == 'all') {
            $items['subjects'] = $this->bookSubjects(true);
            $items['writers'] = $this->bookWriters(true);
            $items['publishers'] = $this->bookPublisher(true);
        } else if ($type == 'writers') {
            $items['writers'] = $this->bookWriters(true);
        } else if ($type == 'subjects') {
            $items['subjects'] = $this->bookSubjects(true);
        } else if ($type == 'publishers') {
            $items['publishers'] = $this->bookPublisher(true);
        }

        return $items;
    }



    public function recentViewBooks()
    {
        $items = request('items');
        $items = $items ? explode(',', $items) : [];

        $books = Book::with(['writers'])
            ->where('available', 'yes')
            ->whereIn('id', $items)
            ->get();

        return BookResource::collection($books, ['simple', 'writers'])->response()->getData(true);
    }


    //    ============ Blow methods not tested =================


    public function featured_products(): array
    {
        $limit = request('limit', 15);
        $query = Product::query()
            ->where('status', 'publish')
            ->where('featured', 1)
            ->paginate($limit);

        $data['products'] = BookResource::collection($query)->response()->getData(true);
        return $data;
    }

    public function loving_products(): array
    {
        $limit = request('limit', 15);
        $query = Product::query()
            ->where('status', 'publish')
            ->whereHas('wishlist')
            ->paginate($limit);

        return BookResource::collection($query)->response()->getData(true);
    }

    public function new_products(): array
    {
        $limit = request('limit', 15);
        $query = Product::query()
            ->where('status', 'publish')
            ->orderByDesc('id')
            ->paginate($limit);

        return BookResource::collection($query)->response()->getData(true);
    }

    public function related_products($item_sku): array
    {
        $totalItems = 7;
        $book = Book::where('url_key', $item_sku)->first();

        $recentItems = collect([]);
        if ($book && $book->more_books_priority) {
            $ids = $book->more_books_priority;
            $priority_ids = $ids ? explode(',', $ids) : [];
            if (count($priority_ids) > 0) {
                $recentItems = Book::with('writers')
                    ->whereIn('id', $priority_ids)
                    ->orderByRaw(DB::raw("FIELD(id, $ids)"))
                    ->get();
            }
        }

        if (count($recentItems) < $totalItems) {
            $limit = $totalItems - count($recentItems);

            $recentItems2 = Book::with('writers')
                ->where('id', '!=', $book->id)
                ->whereHas('writers', function ($query) use ($book) {
                    $query->whereIn('writer_id', $book->writers->pluck('id'));
                })
                ->latest()
                ->limit($limit)
                ->get();

            $recentItems2 = $recentItems->merge($recentItems2);

            if (count($recentItems2) < $limit) {
                $limit2 = $limit - count($recentItems2);
                $recentItems3 = Book::with('writers')
                    ->where('id', '!=', $book->id)
                    ->whereHas('publishers', function ($query) use ($book) {
                        $query->whereIn('publisher_id', $book->publishers->pluck('id'));
                    })
                    ->latest()
                    ->limit($limit2)
                    ->get();
                $recentItems = $recentItems->merge($recentItems3);
            }
        }


        return BookResource::collection($recentItems, ['simple', 'writers'])->response()->getData(true);
    }

    public function category_products($slug): array
    {
        $limit = request('limit', 24);
        $sort = request('sort');
        $min = request('min');
        $max = request('max', 999999999);

        $taxonomy = Taxonomy::query()
            ->with(['children' => function ($children) {
                $children->with('children');
            }])
            ->where('slug', $slug)
            ->first();

        $ids = taxonomyWithChildren($taxonomy);

        $query = Product::with('taxonomies')
            ->where('status', 'publish')
            ->withCount('orderItems')
            ->whereHas('taxonomies', function ($taxonomy) use ($ids) {
                $taxonomy->whereIn('taxonomy_id', $ids);
            });

        if ($min) {
            $query = $query->whereBetween('discount_price', [$min, $max]);
        }

        if ($sort == 'low-to-high') {
            $query = $query->orderBy('discount_price');
        } else if ($sort == 'high-to-low') {
            $query = $query->orderBy('discount_price', 'desc');
        } else if ($sort == 'best-sale') {
            $query = $query->orderBy('order_items_count');
        }

        $query = $query->paginate($limit);
        $related = Taxonomy::query()
            ->whereNotNull('active')
            ->select('id', 'name', 'slug', 'icon', 'featured', 'top', 'type', 'parent_id', 'order', 'options', 'menu_type', 'url')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        return [
            'product' => BookResource::collection($query)->response()->getData(true),
            'related' => $related,
        ];
    }

    public function recent_view_products()
    {
        $recent_token = request('recent_view');
        $product_uuid = request('product_uuid');
        $limit = request('limit', 24);

        if ($product_uuid) {
            $hasProduct = Product::query()
                ->where('uuid', $product_uuid)
                ->where('status', 'publish')
                ->first();
            if ($hasProduct) {
                $logData = RecentViewLog::query()
                    ->where('token', $recent_token)
                    ->where('product_id', $hasProduct->id)
                    ->first();
                $recentLog = $logData ?: new RecentViewLog();
                $recentLog->token = $recent_token;
                $recentLog->product_id = $hasProduct->id;
                $recentLog->save();
            }
        }

        $resents = RecentViewLog::query()
            ->with(['product' => function ($item) {
                $item->where('status', 'publish')->whereNull('deleted_at');
            }])
            ->where('token', $recent_token)
            ->whereHas('product')
            ->orderByDesc('id')
            ->paginate($limit);

        return RecentViewProductResource::collection($resents, ['product'])->response()->getData(true);
    }

    public function search_suggestions(): array
    {
        $keyWord = request('keyword');
        $keyWord = str_replace(' ', '%', $keyWord);

        $category = Taxonomy::query()
            ->where('name', 'like', "%$keyWord%")
            ->whereNotNull('active')
            ->limit(4)
            ->get();

        $products = Product::query()
            ->where('status', 'publish')
            ->where('name', 'like', "%$keyWord%")
            ->orWhere('sku', 'like', "%$keyWord%")
            ->orWhere('short_description', 'like', "%$keyWord%")
            ->whereNotNull('active')
            ->limit(6)
            ->get();

        return [
            'category' => $category,
            'products' => $products
        ];
    }

    public function search_result(): array
    {
        $limit = request('limit', 15);
        $keyWord = request('search');

        $query = Book::search($keyWord)->paginate($limit);
        return BookResource::collection($query, ['simple'])->response()->getData(true);
    }

    public
    function store($slug): StoreResource
    {
        $store = Seller::query()
            ->with(['user'])
            ->where('slug', $slug)
            ->whereNotNull('active')
            ->first();
        if (!$store) {
            return StoreResource::single(null, ['simple']);
        }

        $limit = request('limit', 20);
        $products = Product::query()
            ->where('status', 'publish')
            ->where('store_id', $store->id)
            ->paginate($limit);

        return StoreResource::single($store, ['simple', 'products' => $products]);
    }

    public
    function uploadProductGalleryUpload(): array
    {
        if (!request()->hasFile('gallery')) {
            return [
                'status' => false,
            ];
        }
        $uuid = request('uuid');
        $hasFile = request()->file('gallery');

        $product = Product::query()->where('uuid', $uuid)->first();
        $data = [
            'uuid' => null,
            'status' => 'done',
            'url' => null,
            'large_url' => null,
            'small_url' => null,
        ];
        if ($hasFile && $product) {
            $pictures = $product->pictures ? json_decode($product->pictures, true) : [];
            $dirName = 'product/' . $product->sku;
            $name = 'product-' . time();
            $large_url = store_picture($hasFile, $dirName, $name, false, true);
            $smName = 'product-sm-' . time();
            $small_url = store_picture($hasFile, $dirName, $smName, false, true, 150);
            $uuid = Uuid::uuid4();
            if ($large_url && $small_url) {
                $pictures[] = [
                    'uuid' => $uuid,
                    'status' => 'done',
                    'url' => $large_url,
                    'large_url' => $large_url,
                    'small_url' => $small_url,
                ];
            }

            $product->pictures = json_encode($pictures);
            $product->save();

            //            $data['uuid'] = $uuid;
            //            $data['status'] = 'done';
            //            $data['url'] = asset($small_url);
            //            $data['large_url'] = asset($small_url);
            //            $data['small_url'] = asset($small_url);
            //            $data['response'] = [
            //                'status' => 'success'
            //            ];

            $data = [
                "uuid" => $uuid,
                "status" => "done",
                "name" => $smName,
                "size" => 1024,
                "url" => asset($small_url)
            ];
        }

        return $data;
    }
}
