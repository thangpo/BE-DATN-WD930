<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Appoinment;
use Illuminate\Http\Request;
use App\Models\AvailableTimeslot;
use App\Http\Controllers\Controller;

class AdminAppoinmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listAppoinment = Appoinment::query()->orderBy('updated_at', 'desc')->get();
        $statusAppoinment = Appoinment::status_appoinment;
        $statusPayment = Appoinment::status_payment_method;
        $daysOfWeek = [
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
            7 => 'Chủ Nhật',
        ];
        return view('admin.appoinments.index', compact('listAppoinment', 'statusAppoinment', 'statusPayment', 'daysOfWeek'));
    }
    public function show(string $id)
    {
        $appoinmentDetail = Appoinment::query()->findOrFail($id);
        if ($appoinmentDetail->status_appoinment == 'kham_hoan_thanh') {
            $prescriptionID = $appoinmentDetail->appoinmentHistory->first()->prescription;
        } else {
            $prescriptionID = null;
        }
        // dd($prescriptionID);
        $idA = (int) $prescriptionID;
        $donThuoc = Bill::query()->find($idA);
        // dd($donThuoc);
        $statusAppoinment = Appoinment::status_appoinment;
        $statusPayment = Appoinment::status_payment_method;
        $daysOfWeek = [
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
            7 => 'Chủ Nhật',
        ];
        return view('admin.appoinments.show', compact('appoinmentDetail', 'statusAppoinment', 'donThuoc', 'statusPayment', 'daysOfWeek'));
    }
    public function update(Request $request, string $id)
    {
        $appoinment = Appoinment::query()->findOrFail($id);

        $currentStatus = $appoinment->status_appoinment;

        $newStatus = $request->input('status_appoinment');
        $today = now()->startOfDay();
        $appoinmentDate = Carbon::parse($appoinment->appointment_date)->startOfDay();
        $restrictedStatus = ['dang_kham', 'kham_hoan_thanh', 'can_tai_kham', 'benh_nhan_khong_den'];
        $status = ['da_xac_nhan'];
        // dd($appoinment,$currentStatus,$newStatus);
        $status = array_keys(Appoinment::status_appoinment);
        // dd($appoinment->status_appoinment);
        // if ($currentStatus === Appoinment::HUY_LICH_HEN || $currentStatus === Appoinment::BENH_NHAN_KHONG_DEN) {
        //     return redirect()->route('admin.appoinments.index')->with('error', 'Không thể thay đổi');
        // }
        if ($appoinmentDate > $today && in_array($newStatus, $restrictedStatus)) {
            return redirect()->route('admin.appoinments.index')->with('error', 'Không thể cập nhật trạng thái bị hạn chế trước ngày khám.');
        } elseif (array_search($newStatus, $status) < array_search($currentStatus, $status)) {
            return redirect()->route('admin.appoinments.index')->with('error', 'Không thể quay ngược trạng thái.');
        } else {
            if ($newStatus === Appoinment::KHAM_HOAN_THANH) {
                $appoinment->status_payment_method = Appoinment::DA_THANH_TOAN;
            }
            if ($newStatus === Appoinment::HUY_LICH_HEN) {
                // dd($appoinment->available_timeslot_id);
                $time = AvailableTimeslot::where('id', $appoinment->available_timeslot_id)->first();
                $time->isAvailable = 1;
                $time->save();
            }
            $appoinment->status_appoinment = $newStatus;
            $appoinment->save();
            return redirect()->route('admin.appoinments.index')->with('success', 'Thay đổi trạng thái thành công.');
        }
    }
    public function update1(Request $request, string $id)
    {
        $appoinment = Appoinment::query()->findOrFail($id);

        $currentStatus = $appoinment->status_appoinment;

        $newStatus = $request->input('status_appoinment');
        $today = now()->startOfDay();
        $appoinmentDate = Carbon::parse($appoinment->appointment_date)->startOfDay();
        $restrictedStatus = ['dang_kham', 'kham_hoan_thanh', 'can_tai_kham', 'benh_nhan_khong_den'];
        $status = ['da_xac_nhan'];
        // dd($appoinment,$currentStatus,$newStatus);
        $status = array_keys(Appoinment::status_appoinment);
        // dd($appoinment->status_appoinment);
        // if ($currentStatus === Appoinment::HUY_LICH_HEN || $currentStatus === Appoinment::BENH_NHAN_KHONG_DEN) {
        //     return redirect()->route('admin.appoinments.index')->with('error', 'Không thể thay đổi');
        // }
        if ($appoinmentDate > $today && in_array($newStatus, $restrictedStatus)) {
            return redirect()->back()->with('error', 'Không thể cập nhật trạng thái bị hạn chế trước ngày khám.');
        } elseif (array_search($newStatus, $status) < array_search($currentStatus, $status)) {
            return redirect()->back()->with('error', 'Không thể quay ngược trạng thái.');
        } else {
            if ($newStatus === Appoinment::KHAM_HOAN_THANH) {
                $appoinment->status_payment_method = Appoinment::DA_THANH_TOAN;
            }
            if ($newStatus === Appoinment::HUY_LICH_HEN) {
                
                $time = AvailableTimeslot::where('id', $appoinment->available_timeslot_id)->first();
                $time->isAvailable = 1;
                $time->save();
            }
            $appoinment->status_appoinment = $newStatus;
            $appoinment->save();
            return redirect()->back()->with('success', 'Thay đổi trạng thái thành công.');
        }
    }
}
