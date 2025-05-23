<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Reviews;
use App\Models\Courses;

class ReviewController extends Controller
{
    //

    public function createReview(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|integer|exists:courses,course_id',
                'user_id' => 'required|integer|exists:users,id', // Corrected to match the users table's id column
                'rating' => 'required|integer|min:1|max:5',
                'review_description' => 'required|string|max:1000',
            ]);

            // Check if review already exists
            $existingReview = Reviews::where('course_id', $validated['course_id'])
                ->where('user_id', $validated['user_id']) // Corrected to use user_id
                ->first();

            if ($existingReview) {
                return ApiResponse::clientError('Review already exists', null, 409);
            }
            $review = Reviews::create([
                ...$validated,
                'course_name' => Courses::find($validated['course_id'])->course_name,
                'user_name' => auth()->user()->name,
                'is_approved' => false,
            ]);

            return ApiResponse::success('Review created successfully', $review);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to create review: ' . $th->getMessage(), null, 500);
        }
    }

    public function getReviews()
    {
        try {

            // find all review that are not approved
            $unapprovedReviews = Reviews::where('is_approved', false)->get();
            // course and user name details
            $unapprovedReviews->transform(function ($review) {
                $review->course_name = Courses::find($review->course_id)->course_name;
                $review->user_name = User::find($review->user_id)->name;
                return $review;
            });
            if ($unapprovedReviews->isEmpty()) {
                return ApiResponse::clientError('No reviews found', null, 404);
            }
            return ApiResponse::success('Reviews retrieved successfully', $unapprovedReviews);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve reviews: ' . $th->getMessage(), null, 500);
        }
    }
    public function getReviewsBySubjectId($subject_id)
    {
        try {
            $reviews = Reviews::where('subject_id', $subject_id)->get();
            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No reviews found for this subject', null, 404);
            }
            return ApiResponse::success('Reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve reviews: ' . $th->getMessage(), null, 500);
        }
    }

    public function getReviewNotApproved()
    {
        try {
            $reviews = Reviews::where('is_approved', false)->get();
            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No unapproved reviews found', null, 404);
            }
            return ApiResponse::success('Unapproved reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve unapproved reviews: ' . $th->getMessage(), null, 500);
        }
    }
    public function approveReview($review_id)
    {
        try {
            $review = Reviews::find($review_id);
            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->is_approved = true;
            $review->save();

            return ApiResponse::success('Review approved successfully', $review);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to approve review: ' . $th->getMessage(), null, 500);
        }
    }

    public function deleteReview($review_id)
    {
        try {
            $review = Reviews::find($review_id);
            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->delete();

            return ApiResponse::success('Review deleted successfully');
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to delete review: ' . $th->getMessage(), null, 500);
        }
    }
}
