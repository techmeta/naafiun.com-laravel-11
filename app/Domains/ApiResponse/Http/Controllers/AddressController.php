<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\AddressService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public AddressService $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function AllAddress(): JsonResponse
    {
        $data = $this->addressService->list();
        return $this->success($data, 'address fetched successfully');
    }

    public function StoreNewAddress(): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string|max:80',
            'phone' => 'required|string|max:20',
            'city' => 'required|string|max:55',
            'zip_code' => 'required|string|max:10',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'address validation failed', 422);
        }

        $data = $this->addressService->store();
        return $this->success($data, 'address processed successfully');
    }

    public function deleteAddress(Request $request): JsonResponse
    {
        $data = $this->addressService->delete($request);
        return $this->success($data, 'address deleted successfully');
    }
}
