<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Coupons;
use App\Helpers\ApiResponse;

class CouponController extends Controller
{
    public function index()
    {
        try {
            $coupons = Coupons::orderByDesc('created_at')->get();
            return ApiResponse::success('Coupons fetched successfully', $coupons);
        } catch (\Exception $e) {
            return ApiResponse::serverError('Failed to fetch coupons', $e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'coupon_code' => 'required|unique:coupons,coupon_code',
                'coupon_type' => 'required|in:fixed,percent',
                'value' => 'required|numeric|min:1',
                'expires_at' => 'nullable|date|after:now',
            ]);

            $coupon = Coupons::create([
                'coupon_code' => $validated['coupon_code'],
                'coupon_type' => $validated['coupon_type'],
                'value' => $validated['value'],
                'expires_at' => $validated['expires_at'] ?? null,
            ]);

            return ApiResponse::success('Coupon created successfully', $coupon, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::clientError('Validation failed', $e->errors(), 422);

        } catch (\Exception $e) {
            return ApiResponse::serverError('An unexpected error occurred', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCoupon(Request $request, $id)
    {
        try {
            $coupon = Coupons::find($id);

            if (!$coupon) {
                return ApiResponse::clientError('Coupon not found', null, 404);
            }

            $coupon->delete();

            return ApiResponse::success('Coupon deleted successfully', null, 200);

        } catch (\Exception $e) {
            return ApiResponse::serverError('An unexpected error occurred', [
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $coupon = Coupons::find($id);

            if (!$coupon) {
                return ApiResponse::clientError('Coupon not found', null, 404);
            }

            $validated = $request->validate([
                'coupon_type' => 'sometimes|in:fixed,percent',
                'value' => 'sometimes|numeric|min:1',
                'expires_at' => 'nullable|date|after:now',
            ]);

            $coupon->update($validated);

            return ApiResponse::success('Coupon updated successfully', $coupon);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::clientError('Validation failed', $e->errors(), 422);

        } catch (\Exception $e) {
            return ApiResponse::serverError('An unexpected error occurred', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apply(Request $request)
    {
        try {
            $validated = $request->validate([
                'coupon_code' => 'required|string',
                'subtotal' => 'required|numeric|min:0',
            ]);

            $coupon = Coupons::where('code', $validated['code'])->first();

            if (!$coupon) {
                return ApiResponse::clientError('Invalid coupon code', null, 404);
            }

            if (!$coupon->isValid()) {
                return ApiResponse::clientError('Coupon expired or usage limit exceeded', [
                    'coupon_code' => $coupon->code,
                    'expires_at' => $coupon->expires_at,
                ], 400);
            }

            $discount = $coupon->type === 'fixed'
                ? $coupon->value
                : ($validated['subtotal'] * ($coupon->value / 100));

            $discount = min($discount, $validated['subtotal']);

            return ApiResponse::success('Coupon applied', [
                'discount' => round($discount, 2),
                'final_total' => round($validated['subtotal'] - $discount, 2),
                'coupon' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::clientError('Validation failed', $e->errors(), 422);

        } catch (\Exception $e) {
            return ApiResponse::serverError('An unexpected error occurred while applying the coupon', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $coupon = Coupons::where('coupon_code', $request->coupon_code)->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['message' => 'Invalid or expired coupon'], 400);
        }

        $discount = $coupon->coupon_type === 'percent'
            ? ($request->amount * ($coupon->value / 100))
            : $coupon->value;

        $discount = min($discount, $request->amount);
        $discountedPrice = $request->amount - $discount;

        return ApiResponse::success('Coupon validated successfully', [
            'discount' => $discount,
            'discounted_price' => $discountedPrice,
            'discount_percent' =>  $coupon->value,
        ]);
    }


}
