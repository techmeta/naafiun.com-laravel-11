<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\GeneralService;
use App\Domains\Auth\Models\CustomerTaxForm;
use App\Http\Controllers\Controller;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class TaxInfoController extends Controller
{
    use FileUploadTrait;

    public $generalService;

    public function __construct(GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }

    public function downloadFile()
    {
        $path = public_path('pdf/pro-refurb-lab.pdf');
        $fileName = 'kitchentoolsbd-taxform.pdf';
        return response()->download($path, $fileName, ['Content-Type: application/pdf']);
    }

    public function viewPdfTaxFile()
    {
        $user_id = request('token');
        $tax_id = request('tax_id');
        $first_data = CustomerTaxForm::query()
            ->where('user_id', $user_id)
            ->where('id', $tax_id)
            ->first();
        if (!$first_data) {
            abort(404);
        }
        $path = asset($first_data->tax_form);
        return response()->make(file_get_contents($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $path . '"'
        ]);
    }

    public function taxInformation()
    {
        $page = $this->generalService->get_page('tax-form');
        $user_id = auth('sanctum')->id();
        $tax_data = CustomerTaxForm::where('user_id', $user_id)->get();
        return response([
            'page' => $page,
            'tax_data' => $tax_data,
        ]);
    }

    public function uploadTaxForm(Request $request)
    {
        $this->folderName = 'tax-form';
        $this->rule = 'required|mimes:pdf|max:8000';
        $data = $this->saveFiles($request->file('tax_form'));
        return response($data);
    }

    public function uploadTaxInformation(Request $request)
    {
        $state = request('state');
        $exemption = request('exemption');
        $tax_form = request('tax_form');
        $auth_id = auth('sanctum')->id();

        $return = new  CustomerTaxForm();
        $return->state = $state;
        $return->exemption = $exemption;
        $return->tax_form = $tax_form;
        $return->status = 'pending';
        $return->user_id = $auth_id;
        $return->save();

        return response(['status' => true]);
    }

    public function deleteTaxForm(Request $request)
    {
        $item_id = request('item_id');
        $auth_id = auth('sanctum')->id();
        $first_data = CustomerTaxForm::query()->where('user_id', $auth_id)->where('id', $item_id)->first();
        if ($first_data) {
            $first_data->delete();
        }
        return response(['status' => true]);
    }
}
