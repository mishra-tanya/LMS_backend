<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\PhonePeTransactions;
use App\Helpers\ApiResponse;

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

}
