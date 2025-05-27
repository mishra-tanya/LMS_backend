<?php
namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\SubjectReview;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class SubjectReviewController extends Controller
{
    // Create a new subject review
    public function createSubjectReview(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject_id' => 'required|exists:subjects,id',
                'user_id' => 'required|exists:users,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review_description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return ApiResponse::clientError('Validation failed', $validator->errors(), 422);
            }

            $review = SubjectReview::create($request->all());

            return ApiResponse::success('Subject review created successfully', $review, 201);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            } elseif ($th instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return ApiResponse::clientError('Authorization error: ' . $th->getMessage(), null, 403);
            } else {
                return ApiResponse::serverError('Failed to create subject review: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Get all subject reviews
    public function getSubjectReviews()
    {
        try {
            $reviews = SubjectReview::all();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No subject reviews found', null, 404);
            }

            return ApiResponse::success('Subject reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \PDOException) {
                return ApiResponse::serverError('Database connection error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve subject reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Get reviews for a specific subject
    public function getReviewsBySubjectId($subject_id)
    {
        try {
            $reviews = SubjectReview::where('subject_id', $subject_id)->get();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No reviews found for this subject', null, 404);
            }

            return ApiResponse::success('Subject reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to retrieve subject reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Approve a subject review
    public function approveSubjectReview($review_id)
    {
        try {
            $review = SubjectReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Subject review not found', null, 404);
            }

            $review->update(['approved' => true]);

            return ApiResponse::success('Subject review approved successfully', $review);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Review not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to approve subject review: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // Delete a subject review
    public function deleteSubjectReview($review_id)
    {
        try {
            $review = SubjectReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Subject review not found', null, 404);
            }

            $review->delete();

            return ApiResponse::success('Subject review deleted successfully');
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Review not found', null, 404);
            } elseif ($th instanceof \Exception && strpos($th->getMessage(), 'integrity constraint violation') !== false) {
                return ApiResponse::serverError('Cannot delete: this review is referenced by other records', null, 500);
            } else {
                return ApiResponse::serverError('Failed to delete subject review: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Get approved subject reviews
    public function getApprovedSubjectReviews()
    {
        try {
            $reviews = SubjectReview::where('approved', true)->get();

            if ($reviews->isEmpty()) {
                return ApiResponse::clientError('No approved subject reviews found', null, 404);
            }

            return ApiResponse::success('Approved subject reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \PDOException) {
                return ApiResponse::serverError('Database connection error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve approved subject reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }
}