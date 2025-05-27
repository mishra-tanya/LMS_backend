<?php
namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\CourseReview;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class CourseReviewController extends Controller
{
    // Create a new course review
    public function createCourseReview(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'user_id' => 'required|exists:users,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review_description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return ApiResponse::clientError('Validation failed', $validator->errors(), 422);
            }

            $review = CourseReview::create($request->all());

            return ApiResponse::success('Course review created successfully', $review, 201);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            } elseif ($th instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return ApiResponse::clientError('Authorization error: ' . $th->getMessage(), null, 403);
            } else {
                return ApiResponse::serverError('Failed to create course review: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Get all course reviews
    public function getCourseReviews()
    {
        try {
            $reviews = CourseReview::all();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No course reviews found', null, 404);
            }

            return ApiResponse::success('Course reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \PDOException) {
                return ApiResponse::serverError('Database connection error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve course reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Get reviews for a specific course
    public function getReviewsByCourseId($course_id)
    {
        try {
            $reviews = CourseReview::where('course_id', $course_id)->get();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No reviews found for this course', null, A404);
            }

            return ApiResponse::success('Course reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Course not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to retrieve course reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Approve a course review
    public function approveCourseReview($review_id)
    {
        try {
            $review = CourseReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Course review not found', null, 404);
            }

            $review->update(['approved' => true]);

            return ApiResponse::success('Course review approved successfully', $review);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Review not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to approve course review: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Delete a course review
    public function deleteCourseReview($review_id)
    {
        try {
            $review = CourseReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Course review not found', null, 404);
            }

            $review->delete();

            return ApiResponse::success('Course review deleted successfully');
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Review not found', null, 404);
            } elseif ($th instanceof \Exception && strpos($th->getMessage(), 'integrity constraint violation') !== false) {
                return ApiResponse::serverError('Cannot delete: this review is referenced by other records', null, 500);
            } else {
                return ApiResponse::serverError('Failed to delete course review: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Get approved course reviews
    public function getApprovedCourseReviews()
    {
        try {
            $reviews = CourseReview::where('approved', true)->get();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No approved course reviews found', null, 404);
            }

            return ApiResponse::success('Approved course reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \PDOException) {
                return ApiResponse::serverError('Database connection error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve approved course reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }
}