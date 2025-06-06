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
        $paymentType = $request->payment_type ?? 'phonepe';
        $course_or_subject_id = $request->course_or_subject_id ;
        $amount = $request->amount;

        if (!$amount || !is_numeric($amount) || $amount <= 0) {
            return ApiResponse::clientError('Invalid or missing amount.', null, 422);
        }

        PhonePeTransactions::create([
            'user_id' => $userId,
            'payment_type' => $paymentType,
            'course_or_subject_id' => $course_or_subject_id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'status' => 'initiated',
        ]);

        $data = [
            'merchantId' => env('PHONEPE_MERCHANT_ID'),
            'merchantTransactionId' => $transactionId,
            'merchantUserId' =>  'user_' . $userId,
            'amount' => $amount * 100, 
            'redirectUrl' => route('phonepe.callback'),
            'redirectMode' => 'POST',
            'callbackUrl' => route('phonepe.callback'),
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];
        $encodedPayload = base64_encode(json_encode($data));

        $saltKey = env('PHONEPE_SALT_KEY');
        $saltIndex = 1;

        $path = '/pg/v1/pay';
        $url =env('PHONEPE_URL'); 

        $stringToHash = $encodedPayload . $path . $saltKey;
        $sha256Hash = hash('sha256', $stringToHash);
        $finalXHeader = $sha256Hash . "###" . $saltIndex;
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $finalXHeader
        ])->post($url, ['request' => $encodedPayload]);
         
        $rData = $response->json();

        if (isset($rData['data']['instrumentResponse']['redirectInfo']['url'])) {
            return ApiResponse::success('PhonePe payment URL generated', [
                'redirect_url' => $rData['data']['instrumentResponse']['redirectInfo']['url']
            ]);
        } else {
            Log::error('PhonePe payment initiation failed', $rData);
            return ApiResponse::serverError('Failed to initiate PhonePe payment', $rData, 500);
        }
    }



    public function callback(Request $request){
        $payload = $request->all();
        $frontendUrl = env('APP_BASE_PATH');

// dd($payload);
        try {
            $status = $payload['code'] ?? null;
            $transactionId = $payload['transactionId'] ?? null;
            $merchantTransactionId = $payload['providerReferenceId'] ?? null;
            if (!$merchantTransactionId) {
                return ApiResponse::clientError('Missing transaction reference.', $payload, 400);
            }

            $phonepePayment = PhonePeTransactions::where('transaction_id', $transactionId)->first();

            if (!$phonepePayment) {
                return ApiResponse::clientError('Transaction not found.', $payload, 404);
            }

            if ($status === 'PAYMENT_SUCCESS') {
                $phonepePayment->status = 'success';
                $phonepePayment->merchant_transaction_id =  $merchantTransactionId;
                
                $phonepePayment->save();
                return redirect()->away("{$frontendUrl}/payment-status?status=success&order_id={$merchantTransactionId}&payment_id={$transactionId}");

                // return ApiResponse::success('Payment successful.', [
                //     'transaction_id' => $transactionId,
                //     'merchant_transaction_id' => $merchantTransactionId,
                // ]);
            }

            if ($status === 'PAYMENT_ERROR') {
                $phonepePayment->status = 'failed';
                $phonepePayment->save();

                return redirect()->away("{$frontendUrl}/payment-status?status=failed&transaction_id={$transactionId}}");

                // return ApiResponse::clientError('Payment failed.', [
                //     'transaction_id' => $transactionId,
                //     'merchant_transaction_id' => $merchantTransactionId,
                // ]);
            }

            if ($status === 'PAYMENT_PENDING') {
                $phonepePayment->status = 'pending';
                $phonepePayment->save();

                return redirect()->away("{$frontendUrl}/payment-status?status=pending&transaction_id={$transactionId}}");

                // return ApiResponse::clientError('Payment Pending.', [
                //     'transaction_id' => $transactionId,
                //     'merchant_transaction_id' => $merchantTransactionId,
                // ]);
            }

            return ApiResponse::clientError('Unknown payment status.', $payload, 400);

        } catch (\Exception $e) {
            return ApiResponse::serverError('Internal error processing payment callback.', [
                'error' => $e->getMessage()
            ]);
        }
    }


}
