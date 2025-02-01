<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\ApiWishlistService;
use App\Domains\ApiResponse\Service\CatalogService;
use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{

    public $apiWishlistService;

    public function __construct(ApiWishlistService $apiWishlistService)
    {
        $this->apiWishlistService = $apiWishlistService;
    }

    public function index(): JsonResponse
    {
        $products = $this->apiWishlistService->list();
        return $this->success($products, 'wishlist fetched successfully');
    }

    /**
     * @throws ValidationException
     */
    public function authWishlistStore(): JsonResponse
    {
        $this->validate(\request(), [
            'uuid' => 'required|string|min:30|max:36:exists,products,uuid',
            'email' => 'required|email|min:10|max:80:exists,users,email',
            'password' => 'required|string|min:6|max:80',
        ]);

        $email = \request('email');
        $password = \request('password');
        $remember = request('remember', true);
        $auth_id = null;
        $token = null;
        if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => true], $remember)) {
            $user = User::query()
                ->where('email', $email)
                ->first();
            $token = $user->createToken($user->email)->plainTextToken;
            $auth_id = $user->id;
        } else {
            return $this->error('', 'Invalid credential', 422);
        }

        $products = $this->apiWishlistService->addToLove($auth_id);
        return $this->success(['items' => $products, 'key' => $token], 'wishlist store successfully');
    }


    /**
     * @throws ValidationException
     */
    public function store(): JsonResponse
    {
        $this->validate(\request(), [
            'uuid' => 'required|string|min:30|max:36:exists,products,uuid',
        ]);

        $auth_id = auth('sanctum')->id();
        $products = $this->apiWishlistService->addToLove($auth_id);
        return $this->success($products, 'wishlist store successfully');
    }

    /**
     * @throws ValidationException
     */
    public function delete(): JsonResponse
    {
        $this->validate(\request(), [
            'uuid' => 'required|string|min:30|max:36:exists,products,uuid',
        ]);

        $products = $this->apiWishlistService->delete();
        return $this->success($products, 'wishlist delete successfully');
    }
}
