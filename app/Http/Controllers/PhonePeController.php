<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;

use App\Helpers\ApiResponse;

use App\Models\PhonePeTransactions;
use App\Models\Coupons;

class PhonePeController extends Controller
{
    public function initiate(Request $request){
        try {
        $user = Auth::user();

        if (!$user) {
            return ApiResponse::unauthorized('User not authenticated.');
        }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return ApiResponse::unauthorized('Token has expired.');
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return ApiResponse::unauthorized('Token is invalid.');
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return ApiResponse::unauthorized('Token is missing or invalid.');
        }
        $transactionId = (string) Str::uuid();
        $userId = $user->id;
        $paymentType = $request->payment_type;
        $course_or_subject_id = $request->course_or_subject_id ;
        $amount = $request->amount;
        $validated = $this->validateInitiationData($request);
        if (isset($validated['error'])) {
            return $validated['error']; 
        }
        $paymentType = $validated['payment_type'];
        $course_or_subject_id = $validated['course_or_subject_id'];
        $amount = $validated['amount'];
        $couponCode = $request->coupon_code;

        $discount = 0;

        if ($couponCode) {
            $coupon = Coupons::where('coupon_code', $couponCode)->first();

            if (!$coupon || !$coupon->isValid()) {
                return ApiResponse::clientError('Invalid or expired coupon code.');
            }

            $discount = $coupon->coupon_type === 'percent'
                ? ($amount * ($coupon->value / 100))
                : $coupon->value;

            $discount = min($discount, $amount);

            $amount -= $discount;
        }

        PhonePeTransactions::create([
            'user_id' => $userId,
            'payment_type' => $paymentType,
            'course_or_subject_id' => $course_or_subject_id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'initiated',
            'coupon_code' => $couponCode,
        ]);

        $authResponse = Http::asForm()->post(env('PHONEPE_AUTH_URL'), [
            'client_id' => env('PHONEPE_CLIENT_ID'),
            'client_version' =>  env('PHONEPE_CLIENT_VERSION'),
            'client_secret' => env('PHONEPE_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
        ]);

        if (!$authResponse->ok()) {
            return ApiResponse::serverError('Failed to authenticate with PhonePe.', $authResponse->json());
        }

        $accessToken = $authResponse['access_token'];
        $payUrl = env('PHONEPE_BASE_URL') . '/checkout/v2/pay';
        $paymentPayload = [
            'merchantOrderId' => $transactionId,
            'amount' => $amount * 100, 
            'expireAfter' => 1200,
            'metaInfo' => [
                'udf1' => 'user_id_' . $userId,
                'udf2' => 'course_' . $course_or_subject_id,
            ],
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Payment for your course',
                'merchantUrls' => [
                    'redirectUrl' => route('phonepe.callback') . '?transactionId=' . $transactionId,
                ],
            ],
        ];

        $paymentResponse = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($payUrl, $paymentPayload);

        $rData = $authResponse->json();

        $responseData = $paymentResponse->json();

        if ($paymentResponse->ok() && isset($responseData['redirectUrl'])) {
            return ApiResponse::success('PhonePe payment URL generated', [
                'redirect_url' => $responseData['redirectUrl']
            ]);
        } else {
            Log::error('PhonePe payment initiation failed', $responseData);
            return ApiResponse::serverError('Failed to initiate PhonePe payment', $responseData, 500);
        }
    }

    public function callback(Request $request)
    {
        $transactionId = $request->query('transactionId'); 

        $frontendUrl = env('APP_BASE_PATH');

        if (!$transactionId) {
            return redirect()->away("{$frontendUrl}/payment-status?status=error&reason=missing_transaction_id");
        }

        $authResponse = Http::asForm()->post(env('PHONEPE_AUTH_URL'), [
            'client_id' => env('PHONEPE_CLIENT_ID'),
            'client_version' =>  env('PHONEPE_CLIENT_VERSION'),
            'client_secret' => env('PHONEPE_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
        ]);

        if (!$authResponse->ok()) {
            return redirect()->away("{$frontendUrl}/payment-status?status=error&reason=auth_failed");
        }

        $accessToken = $authResponse['access_token'];

        $statusResponse = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->get(env('PHONEPE_BASE_URL') . "/checkout/v2/order/{$transactionId}/status");

        if (!$statusResponse->ok()) {
            return redirect()->away("{$frontendUrl}/payment-status?status=error&reason=status_check_failed");
        }

        $statusData = $statusResponse->json();
        $status = $statusData['state'] ?? 'UNKNOWN';
        $merchantTransactionId = $statusData['paymentDetails'][0]['transactionId'] ?? null;

        $phonepePayment = PhonePeTransactions::where('transaction_id', $transactionId)->first();
        if ($phonepePayment) {
            $phonepePayment->merchant_transaction_id = $merchantTransactionId;

            if ($status === 'COMPLETED') {
                $phonepePayment->status = 'success';
            } elseif ($status === 'FAILED') {
                $phonepePayment->status = 'failed';
            } elseif ($status === 'PENDING') {
                $phonepePayment->status = 'pending';
            } else {
                $phonepePayment->status = 'unknown';
            }

            $phonepePayment->save();
        }

        return redirect()->away("{$frontendUrl}/payment-status?status={$phonepePayment->status}&order_id={$merchantTransactionId}&payment_id={$transactionId}");
    }

    private function validateInitiationData(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|string',
            'course_or_subject_id' => 'required|integer',
            'amount' => 'required|numeric|min:1',
            'coupon_code' => 'nullable|string',
        ]);
        
        if($validated['payment_type']!='course' && $validated['payment_type']!='subject' ){
            return [
                'error' => ApiResponse::clientError("Is not valid.", null, 422)
            ];
        }

        $table = $validated['payment_type'] === 'course' ? 'courses' : 'subjects';
        $column = $validated['payment_type'] === 'course' ? 'course_id' : 'subject_id';

        $exists = \DB::table($table)->where($column, $validated['course_or_subject_id'])->exists();

        if (!$exists) {
            return [
                'error' => ApiResponse::clientError("The specified {$validated['payment_type']} ({$validated['course_or_subject_id']}) does not exist.", null, 422)
            ];
        }

        return $validated;
    }



}
