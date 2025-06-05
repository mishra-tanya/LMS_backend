<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\ApiResponse;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PhonePeTransactions;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\Chapters;
use App\Models\CourseReview;
use App\Models\SubjectReview;

class StatisticsController extends Controller
{
    // basic stats
    public function getStatistics(){

        try {
            $totalUsers = User::count();

            $newUserStats = User::select(
                DB::raw("COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_users_today"),
                DB::raw("COUNT(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as new_users_this_week"),
                DB::raw("COUNT(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 END) as new_users_this_month")
            )->first();

            $newUsersToday =  $newUserStats->new_users_today;
            $newUsersThisWeek =  $newUserStats->new_users_this_week;
            $newUsersThisMonth =  $newUserStats->new_users_this_month;

            $totalPurchasingUsers = PhonePeTransactions::where('status', 'success')
            ->distinct('user_id')
            ->count('user_id');

            $totalCourses = Courses::count();

            $totalSubjects = Subjects::count();

            $totalChapters = Chapters::count();

            $transactionStats = PhonePeTransactions::select(
                    DB::raw("COUNT(CASE WHEN status = 'success' THEN 1 END) as total_success"),
                    DB::raw("SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_revenue"),
                    DB::raw("COUNT(CASE WHEN status = 'failed' THEN 1 END) as total_failed"),
                    DB::raw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as total_pending"),
                    DB::raw("COUNT(CASE WHEN status = 'success' AND DATE(purchased_at) = CURDATE() THEN 1 END) as purchases_today"),
                    DB::raw("SUM(CASE WHEN status = 'success' AND DATE(purchased_at) = CURDATE() THEN amount ELSE 0 END) as revenue_today"),
                    DB::raw("SUM(CASE WHEN status = 'success' AND YEARWEEK(purchased_at, 1) = YEARWEEK(CURDATE(), 1) THEN amount ELSE 0 END) as revenue_this_week"),
                    DB::raw("SUM(CASE WHEN status = 'success' AND YEAR(purchased_at) = YEAR(CURDATE()) AND MONTH(purchased_at) = MONTH(CURDATE()) THEN amount ELSE 0 END) as revenue_this_month")
                )
                ->first();

            $totalPurchases = $transactionStats->total_success;
            $totalRevenue = $transactionStats->total_revenue;
            $totalFailedTransactions = $transactionStats->total_failed;
            $totalPendingTransactions = $transactionStats->total_pending;
            $totalSuccessTransactionsToday = $transactionStats->purchases_today;
            $totalRevenueToday = $transactionStats->revenue_today;
            $totalRevenueThisWeek = $transactionStats->revenue_this_week;
            $totalRevenueThisMonth = $transactionStats->revenue_this_month;


            $pendingCourseReviews = CourseReview::where('is_approved', false)->count();

            $pendingSubjectReviews = SubjectReview::where('is_approved', false)->count();

            $stats = [
                'total_users' => $totalUsers,
                'new_users_today' => $newUsersToday,
                'total_users_this_week' => $newUsersThisWeek,
                'total_users_this_month' => $newUsersThisMonth,
                'total_purchasing_users'=>$totalPurchasingUsers,
                'total_revenue'=>$totalRevenue,
                'total_failed_transactions'=>$totalFailedTransactions,
                'total_pending_transactions'=>$totalPendingTransactions,
                'total_success_purchase_today' => $totalSuccessTransactionsToday,
                'total_purchases' => $totalPurchases,
                'total_revenue_today' => $totalRevenueToday,
                'total_revenue_this_week' => $totalRevenueThisWeek,
                'total_revenue_this_month' => $totalRevenueThisMonth,
                'total_courses' => $totalCourses,
                'total_subjects' => $totalSubjects,
                'total_chapters' => $totalChapters,
                'pending_course_reviews' => $pendingCourseReviews,
                'pending_subject_reviews' => $pendingSubjectReviews,
            ];

            return ApiResponse::success('Statistics fetched successfully.', $stats);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to fetch stats: ' . $th->getMessage());
        }
    }
}
