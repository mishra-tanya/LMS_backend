<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\ApiResponse;

use App\Models\User;
use App\Models\PhonePeTransactions;
use App\Models\Courses;
use App\Models\CourseReview;
use App\Models\SubjectReview;

class StatisticsController extends Controller
{
    // basic stats
    public function getStatistics(){

        try {
            $totalUsers = User::count();

            $totalPurchasingUsers = PhonePeTransactions::where('status', 'success')
            ->distinct('user_id')
            ->count('user_id');

            $totalCourses = Courses::count();

            $totalPurchases = PhonePeTransactions::where('status', 'success')->count();

            $pendingCourseReviews = CourseReview::where('is_approved', false)->count();

            $pendingSubjectReviews = SubjectReview::where('is_approved', false)->count();

            $stats = [
                'total_users' => $totalUsers,
                'total_purchasing_users'=>$totalPurchasingUsers,
                'total_purchases' => $totalPurchases,
                'total_courses' => $totalCourses,
                'pending_course_reviews' => $pendingCourseReviews,
                'pending_subject_reviews' => $pendingSubjectReviews,
            ];

            return ApiResponse::success('Statistics fetched successfully.', $stats);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to fetch stats: ' . $th->getMessage());
        }
    }
}
