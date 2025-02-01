<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Payment\BraintreePmtService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public BraintreePmtService $pmtService;

    public function __construct(BraintreePmtService $pmtService)
    {
        $this->pmtService = $pmtService;
    }

    public function paymentToken(Request $request)
    {
        $data = $this->pmtService->clientToken($request);
        return response(['token' => $data]);
    }
}
