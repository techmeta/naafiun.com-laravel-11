<?php

namespace App\Domains\Order\Http\Controllers;

use App\Domains\Order\Models\Coupon;
use App\Domains\Order\Models\CouponUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CouponController extends Controller
{

    public function index()
    {
        $coupons = Coupon::query()->latest()->paginate();
        $list = $coupons->pluck('coupon_code', 'coupon_code')->prepend('Off Auto Coupon', '');
        return view('backend.orders.coupon.index', compact('coupons', 'list'));
    }


    public function create()
    {
        return view('backend.orders.coupon.create');
    }


    public function store(Request $request)
    {
        $data = $this->couponValidator();
        $expiry_date = request('expiry_date');
        $data['active'] = request('active') ? request('active') : null;
        $data['expiry_date'] = Carbon::parse($expiry_date)->endOfDay()->toDateTimeString();
        $data['user_id'] = auth()->id();
        Coupon::query()->create($data);
        return redirect()->route('admin.order.coupon.index')->withFlashSuccess('Coupon Created Successfully');
    }

    public function edit(Coupon $coupon)
    {
        return view('backend.orders.coupon.edit', compact('coupon'));
    }


    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->couponValidator($coupon->id);
        $expiry_date = request('expiry_date');
        $data['active'] = request('active') ? request('active') : null;
        $data['expiry_date'] = Carbon::parse($expiry_date)->endOfDay()->toDateTimeString();
        $data['user_id'] = auth()->id();
        $coupon->update($data);
        return redirect()->route('admin.order.coupon.index')->withFlashSuccess('Coupon Updated Successfully');
    }

    public function destroy($id)
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);
        if ($coupon->trashed()) {
            $coupon->forceDelete();
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Permanent Deleted Successfully',
            ]);
        } else if ($coupon->delete()) {
            return \response([
                'status' => true,
                'icon' => 'success',
                'msg' => 'Trashed Successfully',
            ]);
        }

        return \response([
            'status' => false,
            'icon' => 'error',
            'msg' => 'Delete failed',
        ]);
    }

    public function couponValidator(int $id = null): array
    {
        return request()->validate([
            'active' => 'nullable|date|date_format:Y-m-d H:i:s',
            'coupon_code' => 'required|string|max:191|' . $id ? 'unique:coupons,coupon_code,' . $id : 'unique:coupons,coupon_code', // unique coupon code
            'coupon_type' => 'required|string|max:191',
            'coupon_amount' => 'nullable|numeric|min:0',
            'minimum_spend' => 'nullable|numeric|min:0',
            'maximum_spend' => 'nullable|numeric|min:0',
            'limit_per_coupon' => 'nullable|numeric|min:0',
            'limit_per_user' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|date_format:Y-m-d'
        ]);
    }


    public function restore($id)
    {
        Coupon::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.order.coupon.index')->withFlashSuccess('Coupon Restore Successfully');
    }

    public function couponLog()
    {
        $logs = CouponUser::query()->with('user', 'order', 'coupon')->paginate();

        return view('backend.orders.coupon.coupon-log', compact('logs'));
    }
}
