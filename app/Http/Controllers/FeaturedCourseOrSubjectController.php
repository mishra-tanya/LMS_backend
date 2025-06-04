<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\ApiResponse;
use App\Models\PhonePeTransactions;
use App\Models\Courses;
use App\Models\Subjects;

class FeaturedCourseOrSubjectController extends Controller
{
    
    public function getFeaturedCourseOrSubject(){
        try {
            $topItems = PhonePeTransactions::select('payment_type', 'course_or_subject_id')
                ->whereIn('payment_type', ['course', 'subject'])
                ->where('status', 'success')
                ->selectRaw('COUNT(*) as total_users')
                ->groupBy('payment_type', 'course_or_subject_id')
                ->orderByDesc('total_users')
                ->limit(5)
                ->get();

            $results = $topItems->map(function ($item) {
                if ($item->payment_type === 'course') {
                    // $details = Courses::where('course_id', $item->course_or_subject_id)->first();
                    $details = Courses::withAvg('approvedReviews as average_rating', 'rating')
                    ->where('course_id', $item->course_or_subject_id)
                    ->first();

                } else {
                    // $details = Subjects::where('subject_id', $item->course_or_subject_id)->first();
                      $details = Subjects::withAvg('approvedReviews as average_rating', 'rating')
                        ->where('subject_id', $item->course_or_subject_id)
                        ->first();
                }

                return [
                    'type' => $item->payment_type,
                    'total_users' => $item->total_users,
                    'details' => $details,
                ];
            });

            return ApiResponse::success('Top 5 featured courses or subjects retrieved successfully', $results);

        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve top featured items: ' . $th->getMessage());
        }
    }

}
