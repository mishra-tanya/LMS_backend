<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CourseReview;
use App\Models\SubjectReview;
use App\Models\Purchase;
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
            
            $purchases = Purchase::with('course')
                ->where('user_id', $user->id)
                ->get();
            
            if ($purchases->isEmpty()) {
                return ApiResponse::clientError('No purchased courses found', null, 404);
            }
            
            return ApiResponse::success('Purchased courses retrieved successfully', $purchases);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve purchased courses: ' . $th->getMessage(), null, 500);
            }
        }
    }
    
    // Get student's payment history
    public function getPaymentHistory()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            $payments = Payment::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            if ($payments->isEmpty()) {
                return ApiResponse::clientError('No payment history found', null, 404);
            }
            
            return ApiResponse::success('Payment history retrieved successfully', $payments);
        } catch (\Throwable $th) {
            if ($th instanceof QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } else {
                return ApiResponse::serverError('Failed to retrieve payment history: ' . $th->getMessage(), null, 500);
            }
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