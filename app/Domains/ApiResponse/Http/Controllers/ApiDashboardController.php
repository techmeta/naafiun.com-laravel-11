<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\ApiDashboardService;
use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiDashboardController extends Controller
{

    use FileUploadTrait;

    public ApiDashboardService $dashboard;

    public function __construct(ApiDashboardService $ApiDashboardService)
    {
        $this->dashboard = $ApiDashboardService;
    }

    public function orderIndex(): JsonResponse
    {
        $data = $this->dashboard->orders();
        return $this->success($data, 'customer order fetched successfully!');
    }

    public function orderDetails($order_uuid): JsonResponse
    {
        $data = $this->dashboard->orderDetails($order_uuid);
        return $this->success($data, 'customer order details fetched successfully!');
    }

    public function trackingDetails($uuid): JsonResponse
    {
        $data = $this->dashboard->trackingDetails($uuid);
        return $this->success($data, 'order tracking details fetched successfully!');
    }

    public function cancelOrder($uuid): JsonResponse
    {
        $data = $this->dashboard->cancelOrder($uuid);
        return $this->success($data, 'order canceled successfully!');
    }

    /**
     * @throws \Throwable
     */
    public function returnOrder($uuid): JsonResponse
    {
        $validator = Validator::make(request()->all(), [
            'return_reason' => 'required|string|max:255',
            'reason_img' => 'required|image|max:6600|mimes:jpeg,jpg,png,gif,webp',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed', 422);
        }

        $data = $this->dashboard->returnOrder($uuid);
        return $this->success($data, $data['message'] ?? 'something went wrong!');
    }

    public function reserveOrderIndex()
    {
        $data = $this->dashboard->reserve_orders();
        return response($data);
    }

    public function returnIndex()
    {
        $data = $this->dashboard->returns();
        return response($data);
    }

    public function storeCreditIndex()
    {
        $data = $this->dashboard->store_credit();
        return response($data);
    }


    public function returnProcess(Request $request)
    {
        $data = $this->dashboard->return_orders();
        return response($data);
    }

    public function uploadReturnPicture(Request $request)
    {
        $this->folderName = 'returns';
        $this->rule = 'file|max:1000';
        $data = $this->saveFiles($request->file('prof_picture'));
        return response($data);
    }
}
