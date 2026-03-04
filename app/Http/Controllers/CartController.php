<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): Response
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $coupon['discount'] ?? 0;

        return Inertia::render('Cart/Index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'couponCode' => $coupon['code'] ?? null,
            'shipping' => 0,
            'total' => max(0, $subtotal - $discount),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'integer|min:1',
        ]);

        $this->cartService->addItem(
            $request->integer('product_id'),
            $request->integer('variant_id') ?: null,
            $request->integer('quantity', 1),
        );

        return back();
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cartService->updateQuantity(
            $request->string('cart_key'),
            $request->integer('quantity'),
        );

        return back();
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_key' => 'required|string',
        ]);

        $this->cartService->removeItem($request->string('cart_key'));

        return back();
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $result = $this->cartService->applyCoupon($request->string('code'));

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function removeCoupon()
    {
        $this->cartService->removeCoupon();

        return back();
    }
}
