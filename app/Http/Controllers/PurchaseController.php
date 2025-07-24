<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\ApiResponse;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\PhonePeTransactions;

class PurchaseController extends Controller
{
    // all users purchase history
    public function getAllPurchaseHistory(): JsonResponse
    {
        try {
            $history = PhonePeTransactions::with([
                            'user:id,name,email', 
                            'course', 
                            'subject'
                        ])
                        ->orderBy('purchased_at', 'desc')
                        ->get();

            if ($history->isEmpty()) {
                return ApiResponse::clientError('No purchase history found');
            }

            $history = $history->map(function ($item) {
                $item->course_or_subject = null;
                if ($item->payment_type === 'course' && $item->course) {
                    $item->course_or_subject = $item->course;
                } elseif ($item->payment_type === 'subject' && $item->subject) {
                    $item->course_or_subject = $item->subject;
                }

                unset($item->course);
                unset($item->subject);

                return $item;
            });

            return ApiResponse::success('Purchase history fetched successfully', $history);

        } catch (\Exception $e) {
            return ApiResponse::serverError('Failed to fetch purchase history', [
            'error' => $e->getMessage()
            ]);
        }

    }

    // user specific purchase history
    public function getUserPurchaseHistory(): JsonResponse
    {
        try {
            $user  = Auth::user();

            if (!$user) {
                return ApiResponse::unauthorized('User not logged in');
            }

            $history = PhonePeTransactions::with([
                            'user:id,name,email',
                            'course',
                            'subject'
                        ])
                        ->where('user_id', $user->id)
                        ->orderBy('purchased_at', 'desc')
                        ->get();

            if ($history->isEmpty()) {
                return ApiResponse::clientError('No purchase history found');
            }

            $history = $history->map(function ($item) {
                $item->course_or_subject = null;
                if ($item->payment_type === 'course' && $item->course) {
                    $item->course_or_subject = $item->course;
                } elseif ($item->payment_type === 'subject' && $item->subject) {
                    $item->course_or_subject = $item->subject;
                }

                unset($item->course);
                unset($item->subject);

                return $item;
            });

            return ApiResponse::success('Purchase history fetched successfully', $history);

        } catch (\Exception $e) {
            return ApiResponse::serverError('Failed to fetch purchase history', [
                'error' => $e->getMessage()
            ]);
        }
    }

        public function initiateFreeCourseSubject(Request $request)
        {
            $request->validate([
                'payment_type' => 'required|string',
                'id' => 'required|integer',
            ]);

            $user  = Auth::user();

            $userId = $user->id;
            $paymentType = $request->payment_type;
            $id = $request->id; // course_id or subject_id

            // Fetch item (course or subject)
            if ($paymentType === 'course') {
                $item = Courses::find($id);
            } else {
                $item = Subjects::find($id);
            }

            if (!$item) {
                return ApiResponse::clientError("Invalid {$paymentType} ID.");
            }

            $price = $item->price ?? 0;
            $discount = $item->discount ?? 0;
            $discountAmount = ($price * $discount) / 100;
            $finalAmount = $price - $discountAmount;

            if ($finalAmount > 0) {
                return ApiResponse::clientError("This {$paymentType} is not free. Please proceed to payment.");
            }
            $transactionId = $finalAmount <= 0 ? 'FREE-' . uniqid() : generateRealTransactionId();


            // Save to phonepe_transactions
            PhonePeTransactions::create([
                'user_id' => $userId,
                'payment_type' => $paymentType,
                'course_or_subject_id' => $id,
                'transaction_id' => $transactionId,
                'amount' => 0,
                'status' => 'success',
            ]);

            return ApiResponse::success("Free {$paymentType} access granted and transaction recorded.");
        }

}
