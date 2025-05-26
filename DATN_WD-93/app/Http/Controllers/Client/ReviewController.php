<?php

namespace App\Http\Controllers\Client;

use App\Models\Bill;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function store(Request $request, $productId, $billId)
    {
        // Kiểm tra nếu người dùng có thể đánh giá sản phẩm này
        if (!$this->canReviewProduct($productId, $billId)) {
            return redirect()->back()->with('error', 'Bạn chỉ có thể đánh giá các sản phẩm bạn đã mua.');
        }

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này trong hóa đơn cụ thể hay chưa
        if (Review::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->where('bill_id', $billId) // Thêm điều kiện hóa đơn
            ->exists()
        ) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này trong đơn hàng này.');
        }

        // Validate input dữ liệu
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // Tạo mới đánh giá
        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
            'bill_id' => $billId, // Lưu ID hóa đơn
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        // Trả về trang với thông báo thành công
        return redirect()->back()->with('success', 'Bạn đã đánh giá sản phẩm thành công.');
    }


    private function canReviewProduct($productId, $billId)
    {
        $userId = auth()->id();

        // Kiểm tra đơn hàng đã giao
        $purchasedAndDelivered = Bill::where('user_id', $userId)
            ->where('id', $billId)
            ->whereHas('order_detail', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status_bill', Bill::DA_GIAO_HANG)
            ->exists();

        // Kiểm tra đơn hàng đã hủy
        $canceled = Bill::where('user_id', $userId)
            ->where('id', $billId)
            ->whereHas('order_detail', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status_bill', Bill::DA_HUY)
            ->exists();

        // Kiểm tra đơn hàng đang chờ xác nhận
        $purchasedAndPending = Bill::where('user_id', $userId)
            ->where('id', $billId)
            ->whereHas('order_detail', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status_bill', Bill::CHO_XAC_NHAN)
            ->exists();

        // Kiểm tra đơn hàng đang vận chuyển
        $purchasedAndInTransit = Bill::where('user_id', $userId)
            ->where('id', $billId)
            ->whereHas('order_detail', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status_bill', Bill::DANG_VAN_CHUYEN)
            ->exists();

        // Chỉ cho phép đánh giá nếu đã giao hàng và không có trạng thái "ĐÃ HỦY", "CHỜ XÁC NHẬN", "ĐANG VẬN CHUYỂN"
        return $purchasedAndDelivered && !$canceled && !$purchasedAndPending && !$purchasedAndInTransit;
    }
    public function storeRating(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'bill_id' => 'required|exists:bills,id',
        ]);

        // Kiểm tra xem người dùng đã đánh giá sản phẩm trong đơn hàng chưa
        $isRated = Review::where('product_id', $request->product_id)
            ->where('bill_id', $request->bill_id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($isRated) {
            return response()->json(['success' => false, 'message' => 'Bạn chỉ được đánh giá 1 lần.'], 400);
        }

        // Lưu đánh giá
        $rating = new Review();
        $rating->product_id = $request->product_id;
        $rating->bill_id = $request->bill_id;
        $rating->user_id = auth()->id();
        $rating->rating = $request->rating;
        $rating->comment = $request->comment;
        $rating->save();

        return response()->json(['success' => true, 'message' => 'Đánh giá thành công!']);
    }
}
