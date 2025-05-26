<?php

namespace App\Http\Controllers\Client;

use App\Models\Bill;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Specialty;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use App\Models\VariantPackage;
use App\Models\VariantProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function listCart(Request $request)
    {

        $categories = Category::orderBy('name', 'asc')->get();
        $spe = Specialty::whereIn('classification', ['chuyen_khoa', 'kham_tu_xa'])
            ->orderBy('name', 'asc')
            ->get();
        $orderCount = 0; // Mặc định nếu chưa đăng nhập
        if (Auth::check()) {
            $user = Auth::user();
            $orderCount = $user->bill()->count(); // Nếu đăng nhập thì lấy số lượng đơn hàng
        }
        $cart = Cart::where('user_id', Auth::id())->with("items.product", "items.variant")->first();
        // $cart = session()->get('cart', default: []);

        // $tt = $cart['price'] - (($cart['price']  * $cart['discount']) / 100);

        $total = 0;
        $subTotal = 0;
        $shipping = 40000;
        $discount = 0;
        $checkMinDiscount = 0;
        $maxDiscount = 0;
        if ($cart && $cart->items->count() > 0) {
            foreach ($cart->items as  $item) {
                $price = is_numeric($item['price']) ? $item['price'] : 0;
                $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
                // Kiểm tra nếu các khóa cần thiết tồn tại trong mục giỏ hàng
                // Tính toán tổng phụ
                $subTotal += $price * $quantity;
            }
        }

        // Xử lý mã giảm giá nếu có
        $checkTypeDiscount = 0;
        if ($cart && $cart->coupon_code !== null) {
            $discountCheck = $cart->coupon_code;
            $couponCheck = UserCoupon::join('coupons', 'user_coupons.coupon_id', '=', 'coupons.id')
                ->where('user_coupons.user_id', Auth::id()) // Kiểm tra theo user_id
                ->where('coupons.code', $discountCheck) // Kiểm tra theo mã giảm giá
                ->first(); // Lấy tất cả các trường từ bảng user_coupons và coupons           
            $checkTypeDiscount = $couponCheck->type;
            $discount = $couponCheck->value;
            if ($couponCheck->type == 'percentage') {
                $checkTypeDiscount = 'percentage';
                $maxDiscount = $couponCheck->max_discount;
            }
        }
        if ($request->has('coupon_code')) {
            if (request()->query('coupon_code') == 'loaibo') {
                $cart->coupon_code = null;
                $cart->save();
                $discount = 0;
            } else {

                $coupon = UserCoupon::join('coupons', 'user_coupons.coupon_id', '=', 'coupons.id')
                    ->where('user_coupons.user_id', Auth::id()) // Lọc theo user_id
                    ->where('coupons.code', $request->input('coupon_code')) // Kiểm tra mã giảm giá từ request
                    ->first(); // Lấy tất cả các trường từ bảng user_coupons và coupons
                if ($coupon && $coupon->isValid()) {
                    $discount = $coupon->value;
                    $checkMinDiscount = $coupon->min_order_value;
                    if ($checkMinDiscount < ($subTotal + 40000)) {
                        $cart->coupon_code = $coupon->code;
                        $cart->save();
                        if ($coupon->type == 'percentage') {
                            $checkTypeDiscount = 'percentage';
                            $maxDiscount = $coupon->max_discount ?? 0;
                        } else {
                            $checkTypeDiscount = 'fixed';
                        }
                    } else {
                        $fmCheckMinDiscount = number_format($checkMinDiscount, 0, ',', '.');
                        return back()->with('error', 'Mã chỉ áp dụng cho đơn hàng lớn hơn ' . $fmCheckMinDiscount . 'đ');
                    }
                } else {
                    return back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn!');
                }
            }
        }

        $total = $subTotal + $shipping - $discount;
        return view('client.home.cart', compact(
            'orderCount',
            'categories',
            'cart',
            'subTotal',
            'shipping',
            'total',
            'discount',
            'checkTypeDiscount',
            'checkMinDiscount',
            'maxDiscount',
            'spe'
        ));
    }
    public function addCart(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $variantID = $request->input('variantId');
        $productId = $request->input('productId');

        try {
            if ($request->input('variantId')) {
                // dd($variantID,$productId);
                $variantProduct = VariantProduct::query()
                    ->where('id_product', $productId)
                    ->where('id_variant', $variantID)
                    ->firstOrFail();
                // dd($variantProduct);
                // Kiểm tra số lượng sản phẩm biến thể
                if ($variantProduct->quantity <= 0) {
                    return redirect()->back()->with('error', "Sản phẩm đã hết hàng, không thể thêm vào giỏ hàng.");
                }
                if (!$variantProduct) {
                    return redirect()->back()->with('error', "Sản phẩm không tồn tại");
                }
                // Tính toán giá sản phẩm sau khi áp dụng giảm giácod
                $totalPrice = $variantProduct->price - (($variantProduct->price * $variantProduct->product->discount) / 100);
                // Kiểm tra sản phẩm đã tồn tại trong giỏ hàng chưa
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('variant_id', $variantProduct->id)
                    ->first();
                if ($cartItem) {
                    $cartItem->quantity += $request->quantity;
                    // Kiểm tra nếu số lượng vượt quá kho
                    if ($cartItem->quantity > $variantProduct->quantity) {
                        return redirect()->back()->with('error', "Số lượng sản phẩm vượt quá số lượng hiện có trong kho.");
                    }
                    $cartItem->total = $totalPrice * $cartItem->quantity;
                    $cartItem->save(); //
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $variantProduct->product->id,
                        'variant_id' => $variantProduct->id,
                        'name' => $variantProduct->product->name,
                        'image' => $variantProduct->product->img,
                        'price' => $totalPrice,
                        'quantity' => $request->quantity,
                        'total' => $totalPrice * $request->quantity
                    ]);
                }
            } elseif ($request->input('productId')) {
                $product = Product::query()->findOrFail($productId);
                // Kiểm tra số lượng sản phẩm
                if ($product->quantity <= 0) {
                    return redirect()->back()->with('error', "Sản phẩm đã hết hàng, không thể thêm vào giỏ hàng.");
                }

                if (!$product) {
                    return redirect()->with('error', "Sản phẩm không tồn tại");
                }
                // Tính toán giá sản phẩm sau khi áp dụng giảm giácod
                $totalPrice = $product->price - (($product->price * $product->discount) / 100);

                // Check if the product is already in the cart
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($cartItem) {
                    // If the product already exists in the cart, update the quantity and total
                    $cartItem->quantity += $request->quantity; // Update quantity
                    // Kiểm tra nếu số lượng vượt quá kho
                    if ($cartItem->quantity > $product->quantity) {
                        return redirect()->back()->with('error', "Số lượng sản phẩm vượt quá số lượng hiện có trong kho.");
                    }

                    $cartItem->total = $totalPrice * $cartItem->quantity; // Update total price
                    $cartItem->save(); // Save the updated item
                } else {
                    // If it doesn't exist, create a new cart item
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'name' => $product->name, // Store product name
                        'image' => $product->img, // Store product image
                        'price' => $totalPrice, // Store price after discount
                        'quantity' => $request->quantity, // Store quantity
                        'total' => $totalPrice * $request->quantity // Store total price
                    ]);
                }
            }
            return redirect()->back()->with('success', 'Thêm sản phẩm vào giỏ hàng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng!');
        }
    }
    public function updateCart(Request $request)
    {
        $items = $request->input('items');

        if (!$items || !is_array($items)) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ'], 400);
        }

        foreach ($items as $id => $item) {
            $quantity = $item['quantity'] ?? null;

            if (is_null($quantity) || !is_numeric($quantity)) {
                return response()->json(['message' => 'ID hoặc quantity không hợp lệ'], 400);
            }

            $cartItem = CartItem::findOrFail($id);
            $total = $cartItem->price * $quantity;

            // Cập nhật quantity và total
            $cartItem->update([
                'quantity' => $quantity,
                'total' => $total
            ]);
        }

        return response()->json(['message' => 'Giỏ hàng đã được cập nhật thành công'], 200);
    }


    public function removeCart(Request $request)
    {
        $cartItemId = $request->input('id');
        $cartItem = CartItem::find($cartItemId);

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['message' => 'Sản phẩm đã được xóa khỏi giỏ hàng']);
        }

        return response()->json(['message' => 'Item not found'], 404);
    }
    public function reorder($orderId)
    {
        $order = Bill::with('products')->findOrFail($orderId);
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        foreach ($order->order_detail as $orderDetail) {
            // Lấy sản phẩm biến thể từ chi tiết đơn hàng
            $productVariant = VariantProduct::with('product')->findOrFail($orderDetail->variant_id);

            // Lấy discount từ bảng `products`
            $product = $productVariant->product; // Truy cập quan hệ `product`
            $discount = $product->discount ?? 0; // Nếu `discount` là null, đặt mặc định 0

            // Tính toán giá sản phẩm biến thể sau khi áp dụng giảm giá
            $totalPrice = $productVariant->price - (($productVariant->price * $discount) / 100);

            // Kiểm tra nếu sản phẩm đã tồn tại trong giỏ hàng
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('variant_id', $productVariant->id) // Kiểm tra dựa trên `variant_id`
                ->first();

            if ($cartItem) {
                // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng và tổng giá
                $cartItem->quantity += $orderDetail->quantity; // Thêm số lượng theo đơn hàng trước đó
                $cartItem->total = $totalPrice * $cartItem->quantity; // Cập nhật tổng giá
                $cartItem->save();
            } else {
                // Nếu chưa có, tạo một mục giỏ hàng mới
                CartItem::create([
                    'cart_id' => $cart->id,
                    'variant_id' => $productVariant->id, // Lưu `variant_id`
                    'product_id' => $productVariant->id_product, // Liên kết tới sản phẩm chính
                    'name' => $productVariant->product->name,
                    'image' => $productVariant->product->img, // Ảnh từ sản phẩm biến thể
                    'price' => $totalPrice,
                    'quantity' => $orderDetail->quantity, // Số lượng theo đơn hàng trước đó
                    'total' => $totalPrice * $orderDetail->quantity
                ]);
            }
        }

        return redirect()->route('orders.index')->with('success', 'Các sản phẩm từ đơn hàng đã được thêm vào giỏ hàng.');
    }
    public function getPriceQuantiVariant(Request $request)
    {
        $namePakeges = $request->input('namePakeges'); //id san pham
        $idPakage = VariantPackage::where('name', $namePakeges)->select('id')->first();
        $idVp = $idPakage->id;
        if ($idVp) {
            $vp = VariantProduct::where('id_variant', $idVp)->select('quantity', 'price', 'id_variant')->first();
            return response()->json([
                'price' => number_format($vp->price, 0, ',', '.') . ' VNĐ',
                'quantity' => $vp->quantity,
                'id_variant' => $vp->id_variant,
            ]);
        }
        //not found
        return response()->json(['error' => 'Có lỗi đã xảy ra!!!'], 404);
    }
}
