<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\ApiResponse;
use App\Models\PhonePeTransactions;
use App\Models\Courses;
use App\Models\Subjects;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class FeaturedCourseOrSubjectController extends Controller
{
    

    public function getFeaturedCourseOrSubject()
    {
        try {
            $topCourses = PhonePeTransactions::select('payment_type', 'course_or_subject_id')
                ->where('payment_type', 'course')
                ->where('status', 'success')
                ->selectRaw('COUNT(*) as total_users')
                ->groupBy('payment_type', 'course_or_subject_id')
                ->orderByDesc('total_users')
                ->limit(10)
                ->get();

            $topSubjects = PhonePeTransactions::select('payment_type', 'course_or_subject_id')
                ->where('payment_type', 'subject')
                ->where('status', 'success')
                ->selectRaw('COUNT(*) as total_users')
                ->groupBy('payment_type', 'course_or_subject_id')
                ->orderByDesc('total_users')
                ->limit(5)
                ->get();

            // Try to authenticate user (but do not fail if token is missing)
            $user = null;
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                $user = null;
            }
            $topItems = $topCourses->concat($topSubjects)->values();

            $results = $topItems->map(function ($item) use ($user) {
                if ($item->payment_type === 'course') {
                    $details = Courses::withAvg('approvedReviews as average_rating', 'rating')
                        ->withCount('approvedReviews as total_reviews')
                        ->where('course_id', $item->course_or_subject_id)
                        ->first();
                } else {
                    $details = Subjects::withAvg('approvedReviews as average_rating', 'rating')
                        ->withCount('approvedReviews as total_reviews')
                        ->where('subject_id', $item->course_or_subject_id)
                        ->first();
                }

                $isPurchased = false;
                $expiryDaysLeft = null;

                if ($user && $details) {
                    $purchase = PhonePeTransactions::where('user_id', $user->id)
                        ->where('payment_type', $item->payment_type)
                        ->where('course_or_subject_id', $item->course_or_subject_id)
                        ->where('status', 'success')
                        ->latest('purchased_at')
                        ->first();

                    if ($purchase && method_exists($purchase, 'daysLeft')) {
                        $isPurchased = true;
                        $expiryDaysLeft = $purchase->daysLeft();
                    }
                }

                return [
                    'type' => $item->payment_type,
                    'total_users' => $item->total_users,
                    'details' => $details,
                    'is_purchased' => $isPurchased,
                    'expiry_days_left' => $expiryDaysLeft,
                ];
            });

            return ApiResponse::success('Top 5 featured courses or subjects retrieved successfully', $results);

        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve top featured items: ' . $th->getMessage());
        }
    }


}
