<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CourseReview;
use App\Models\SubjectReview;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\PhonePeTransactions;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    // Get student profile
    public function getProfile()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            return ApiResponse::success('Profile retrieved successfully', $user);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Auth\AuthenticationException) {
                return ApiResponse::clientError('Authentication error: ' . $th->getMessage(), null, 401);
            } else {
                return ApiResponse::serverError('Failed to retrieve profile: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Update student profile
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            $validator = \Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone' => 'sometimes|string|max:15',
                'address' => 'sometimes|string|max:255',
            ]);
            
            if ($validator->fails()) {
                return ApiResponse::clientError('Validation failed', $validator->errors(), 422);
            }
            
            // Update user data
            $user->update($request->all());
            
            return ApiResponse::success('Profile updated successfully', $user);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            } elseif ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to update profile: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Change password
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            $validator = \Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);
            
            if ($validator->fails()) {
                return ApiResponse::clientError('Validation failed', $validator->errors(), 422);
            }
            
            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::clientError('Current password is incorrect', null, 422);
            }
            
            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();
            
            return ApiResponse::success('Password changed successfully');
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            } else {
                return ApiResponse::serverError('Failed to change password: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Get student's purchased courses
   public function getPurchasedCourses()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }

            $purchases = PhonePeTransactions::where('user_id', $user->id)
                ->where('status', 'success')
                ->orderByDesc('created_at')
                ->get();

            if ($purchases->isEmpty()) {
                return ApiResponse::clientError('No purchased courses found', null, 404);
            }

            $purchases = $purchases->map(function ($item) {
                $item->course_or_subject_details = null;
                $item->days_left = $item->daysLeft();
                $item->is_expired = !$item->isValid();
                if ($item->payment_type === 'course') {
                    $details = Courses::withAvg('approvedReviews as average_rating', 'rating')
                        ->withCount('approvedReviews as total_reviews')
                        ->where('course_id', $item->course_or_subject_id)
                        ->first();
                } elseif ($item->payment_type === 'subject') {
                    $details = Subjects::withAvg('approvedReviews as average_rating', 'rating')
                        ->withCount('approvedReviews as total_reviews')
                        ->where('subject_id', $item->course_or_subject_id)
                        ->first();
                } else {
                    $details = null;
                }

                if ($details) {
                    $item->course_or_subject_details = $details;
                }

                return $item;
            });

            return ApiResponse::success('Purchased courses retrieved successfully', $purchases);

        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }

            return ApiResponse::serverError('Failed to retrieve purchased courses: ' . $th->getMessage(), null, 500);
        }
    }

    
    // Get student's reviews
    public function getMyReviews()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            $courseReviews = CourseReview::with('course')
                ->where('user_id', $user->id)
                ->get();
                
            $subjectReviews = SubjectReview::with('subject')
                ->where('user_id', $user->id)
                ->get();
            
            $reviews = [
                'course_reviews' => $courseReviews,
                'subject_reviews' => $subjectReviews
            ];
            
            return ApiResponse::success('Reviews retrieved successfully', $reviews);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve reviews: ' . $th->getMessage(), null, 500);
            }
        }
    }
}