<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = Coupon::orderBy('updated_at', 'desc')->get();
        $allCouponsUser = UserCoupon::join('coupons', 'user_coupons.coupon_id', '=', 'coupons.id')
            ->select('user_coupons.coupon_id') // Lấy giá trị coupon_id
            ->where(function ($query) {
                $query->whereNull('coupons.expiry_date') // Chấp nhận mã không có ngày hết hạn
                    ->orWhere('coupons.expiry_date', '>=', Carbon::now()); // Hoặc mã có ngày lớn hơn hoặc bằng hiện tại
            })
            ->distinct()
            ->get();
        return view('admin.coupons.index', compact('coupons', 'allCouponsUser'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $coupons = Coupon::orderBy('updated_at', 'desc')->get();
        return view('admin.coupons.create', compact('coupons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $max_discount = $request->max_discount;
        // Validate dữ liệu nhập vào
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->min_order_value) {
                        $fail('Giá trị giảm giá phải nhỏ hơn hoặc bằng giá trị đơn hàng tối thiểu.');
                    }
                }
            ],
            'min_order_value' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'usage_limit' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
            'type' => 'required',
            'points_required' => 'nullable|numeric|min:0'
        ]);
        if ($request->points_required === null) {
            $request->points_required = 0;
        }
        // Lưu mã giảm giá vào cơ sở dữ liệu
        Coupon::create([
            'code' => $request->code,
            'value' => $request->value,
            'min_order_value' => $request->min_order_value,
            'expiry_date' => $request->expiry_date,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->is_active,
            'type' => $request->type,
            'points_required' => $request->points_required,
            'max_discount' => $max_discount
        ]);

        // Trả về thông báo thành công và chuyển hướng
        return redirect()->route('admin.coupons.index')->with('success', 'Mã giảm giá đã được thêm thành công!');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $allCouponsUser = UserCoupon::join('coupons', 'user_coupons.coupon_id', '=', 'coupons.id')
            ->select('user_coupons.coupon_id') // Lấy giá trị coupon_id
            ->where(function ($query) {
                $query->whereNull('coupons.expiry_date') // Chấp nhận mã không có ngày hết hạn
                    ->orWhere('coupons.expiry_date', '>=', Carbon::now()); // Hoặc mã có ngày lớn hơn hoặc bằng hiện tại
            })
            ->distinct()
            ->get();
        if (!$allCouponsUser->contains('coupon_id', $id)) {
            $coupon = Coupon::findOrFail($id);
            return view('admin.coupons.edit', compact('coupon'));
        } else {
            return redirect()->back()->with('error', 'Đã có người sở hữu mã giảm giá này.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $max_discount = $request->max_discount;
        // Validate dữ liệu nhập vào
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $id,  // Kiểm tra tính duy nhất, bỏ qua mã giảm giá đang sửa
            'value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->min_order_value) {
                        $fail('Giá trị giảm giá phải nhỏ hơn hoặc bằng giá trị đơn hàng tối thiểu.');
                    }
                }
            ],
            'min_order_value' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'usage_limit' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
            'type' => 'required',
            'points_required' => 'nullable|numeric|min:0'
        ]);
        if ($request->points_required === null) {
            $request->points_required = 0;
        }
        // Cập nhật mã giảm giá vào cơ sở dữ liệu
        $coupon = Coupon::findOrFail($id);
        $coupon->update([
            'code' => $request->code,
            'value' => $request->value,
            'min_order_value' => $request->min_order_value,
            'expiry_date' => $request->expiry_date,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->is_active,
            'type' => $request->type,
            'points_required' => $request->points_required,
            'max_discount' => $max_discount
        ]);

        // Trả về thông báo thành công và chuyển hướng
        return redirect()->route('admin.coupons.index')->with('success', 'Mã giảm giá đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $allCouponsUser = UserCoupon::join('coupons', 'user_coupons.coupon_id', '=', 'coupons.id')
            ->select('user_coupons.coupon_id') // Lấy giá trị coupon_id
            ->where(function ($query) {
                $query->whereNull('coupons.expiry_date') // Chấp nhận mã không có ngày hết hạn
                    ->orWhere('coupons.expiry_date', '>=', Carbon::now()); // Hoặc mã có ngày lớn hơn hoặc bằng hiện tại
            })
            ->distinct()
            ->get();
        if (!$allCouponsUser->contains('coupon_id', $id)) {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();
            return redirect()->route('admin.coupons.index')->with('success', 'Mã giảm giá đã được xóa!');
        } else {
            return redirect()->back()->with('error', 'Đã có người sở hữu mã giảm giá này.');
        }
    }
}
