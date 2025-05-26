<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $fillable = [
        'billCode',
        'addressUser',
        'phoneUser',
        'nameUser',
        'emailUser',
        'totalPrice',
        'user_id',
        'status_bill',
        'status_payment_method',
        'moneyProduct',
        'moneyShip'
    ];

    const status_bill = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'dang_chuan_bi' => 'Đang chuẩn bị',
        'dang_van_chuyen' => 'Đang vận chuyển',
        'khach_hang_tu_choi' => 'Khách hàng từ chối',
        'da_giao_hang' => 'Đã giao hàng',
        'da_huy' => 'Đơn hàng đã hủy',
    ];
    const status_payment_method = [
        'chua_thanh_toan' => 'Chưa thanh toán',
        'da_thanh_toan' => 'Đã thanh toán',
    ];
    const CHO_XAC_NHAN = 'cho_xac_nhan';
    const DA_XAC_NHAN = 'da_xac_nhan';
    const DANG_CHUAN_BI = 'dang_chuan_bi';
    const DANG_VAN_CHUYEN = 'dang_van_chuyen';
    const KHACH_HANG_TU_CHOI = 'khach_hang_tu_choi';
    const DA_GIAO_HANG = 'da_giao_hang';
    const DA_HUY = 'da_huy';
    const CHUA_THANH_TOAN = 'chua_thanh_toan';
    const DA_THANH_TOAN = 'da_thanh_toan';
    public function user()
    {
        return $this->belongsTo(User::class); //$this đại diện cho thể hiện hiện tại của lớp Product
        //Phương thức belongsTo của Eloquent ORM được sử dụng để xác định mối quan hệ "belongs to" (thuộc về) giữa mô hình Product và mô hình Category.
    }
    public function order_detail()
    {
        return $this->hasMany(OrderDetail::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_details', 'bill_id', 'product_id')
            ->withPivot('quantity');
    }
    public function getStatusClass()
    {
        switch ($this->status_bill) {
            case self::CHO_XAC_NHAN:
                return 'status-pending';
            case self::DA_XAC_NHAN:
                return 'status-confirmed';
            case self::DANG_CHUAN_BI:
                return 'status-preparing';
            case self::DANG_VAN_CHUYEN:
                return 'status-shipping';
            case self::KHACH_HANG_TU_CHOI:
                return 'status-rejected';
            case self::DA_GIAO_HANG:
                return 'status-delivered';
            case self::DA_HUY:
                return 'status-canceled';
            default:
                return '';
        }
    }
}
