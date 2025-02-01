<?php

namespace App\Domains\Order\Http\Controllers;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{

    public function stock_report()
    {
        return view('backend/orders/report/index');
    }


}
