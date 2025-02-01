<?php

namespace App\Domains\ApiResponse\Http\Controllers;

use App\Domains\ApiResponse\Service\GeneralService;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Notifications\ContactMail;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GeneralController extends Controller
{

    public GeneralService $generalService;

    public function __construct(GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }


    public function downloadFile(): BinaryFileResponse
    {
        $path = public_path('pdf/pro-refurb-lab.pdf');
        $fileName = 'customer-tax-form.pdf';
        return Response::download($path, $fileName, ['Content-Type: application/pdf']);
    }

    public function loginAsCustomer($token): JsonResponse
    {
        $decrypt_id = $token ? decrypt($token) : null;
        $prevToken = request('auth_token');
        $adminToken = request('admin_token');

        if ($prevToken) {
            $user = $this->generalService->getUserFromToken($prevToken);
            if ($user && $user->id !== $decrypt_id) {
                $prevToken = '';
            }
        }

        if ($adminToken || $prevToken) {
            $user = $this->generalService->getUserFromToken($adminToken);
            if ($user && $user->id == $decrypt_id) {
                return $this->success([
                    'status' => true,
                    'token' => $prevToken,
                    'auth_token' => $adminToken,
                ]);
            }
        }


        $user = User::query()
            ->where('id', $decrypt_id)
            ->first();
        $data['status'] = false;
        $data['token'] = '';
        $data['auth_token'] = '';
        if ($user) {
            $user->to_be_logged_out = 0;
            $user->save();
            $authToken = $user->createToken($user->id)->plainTextToken;
            $data['token'] = $authToken;
            if ($user->hasRole('Administrator') || $user->can('view_backend')) {
                $data['auth_token'] = $authToken;
            }
            $data['status'] = true;
        }
        return $this->success($data);
    }

    public function generalSettings(): JsonResponse
    {
        $settings = $this->generalService->allSettings();
        return response()->json(['data' => $settings]);
    }

    public function menus()
    {
        $menus = $this->generalService->all_menus();
        return response([
            'menus' => $menus
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->generalService->allCategories();
        return $this->success($categories, 'category list data');
    }

    public function top_categories(): JsonResponse
    {
        $categories = $this->generalService->top_categories();
        return $this->success($categories, 'top category list data');
    }


    public function banners(): JsonResponse
    {
        $banners = $this->generalService->allBanners();
        return $this->success($banners, 'top category list data');
    }


    public function faqPages(): JsonResponse
    {
        $faqs = $this->generalService->faqPages();
        return $this->success($faqs, 'faqs list data');
    }


    public function singlePages($slug): JsonResponse
    {
        $page = $this->generalService->get_page($slug);
        return $this->success($page, 'data found');
    }


    public function contactMessageSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:155',
            'email' => 'required|email|max:155',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:55',
            'message' => 'required|string|max:400',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $error = '';
            foreach ($errors as $item) {
                $error .= '<p style="color:red; margin-bottom:6px">' . $item . '</p>';
            }

            return response(['status' => false, 'errors' => $error]);
        }

        try {
            $users = User::role('Administrator')->get();
            $requestAll = $request->only(['name', 'email', 'phone', 'subject', 'message']);
            foreach ($users as $user) {
                if ($user->email) {
                    $user->notify(new ContactMail($requestAll));
                }
            }
        } catch (\Exception $e) {
        }
        return response([
            'status' => true,
            'errors' => [],
            'message' => 'Your contact send successfully.'
        ]);
    }
}
