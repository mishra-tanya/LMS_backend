<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\CourseReview;
use App\Models\PhonePeTransactions;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CourseController extends Controller
{
    // get courses
    public function getCourses()
    {
        try {
             $user = null;
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                $user = null;
            }

            $courses = Courses::withAvg('approvedReviews as average_rating', 'rating')
                ->withCount('approvedReviews as total_reviews')
                ->get();

            if ($courses->isEmpty()) {
                return ApiResponse::clientError('No courses found', null, 404);
            }

            if ($user) {
                $purchases = PhonePeTransactions::where('user_id', $user->id)
                    ->where('status', 'success')
                    ->where('payment_type', 'course')
                    ->get()
                    ->keyBy('course_or_subject_id');

                $courses->transform(function ($course) use ($purchases) {
                    if (isset($purchases[$course->course_id])) {
                        $purchase = $purchases[$course->course_id];
                        $course->is_purchased = true;
                        $course->expiry_days_left = $purchase->daysLeft();
                    } else {
                        $course->is_purchased = false;
                        $course->expiry_days_left = null;
                    }

                    return $course;
                });
            }

            return ApiResponse::success('Courses retrieved successfully', $courses);

        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve courses: ' . $th->getMessage(), null, 500);
        }
    }



    // get course by id
    public function getCourseById($id)
    {
        try {
            $course = Courses::find($id);

            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }
            $user = null;
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                $user = null; 
            }

            // Fetch total subjects for the course
            $subjects = Subjects::where('course_id', $id)->get();

            // Fetch total users who bought the course
           $totalUsers = PhonePeTransactions::where('payment_type', 'course')
            ->where('course_or_subject_id', $id)
            ->where('status','success')
            ->count();

            // Calculate overall rating for the course
            $reviewStats = CourseReview::where('course_id', $id)
                ->where('is_approved', true)
                ->selectRaw('AVG(rating) as overall_rating, COUNT(*) as total_review')
                ->first();

            $overallRating = $reviewStats->overall_rating;
            $totalReview = $reviewStats->total_review;
            $isPurchased = false;
            $expiryDaysLeft = null;
            if ($user) {
                $purchase = PhonePeTransactions::where('user_id', $user->id)
                    ->where('payment_type', 'course')
                    ->where('course_or_subject_id', $id)
                    ->where('status', 'success')
                    ->latest('purchased_at')
                    ->first();

                if ($purchase) {
                    $isPurchased = true;
                    $expiryDaysLeft = $purchase->daysLeft();
                }
            }

            // Prepare detailed course information
            $courseDetails = [
                'course_name' => $course->course_name,
                'course_description' => $course->description,
                'price'=> $course->price,
                'discount'=> $course->discount,
                'semester' => $course->semester,
                'image' => $course->image ? url('storage/' . $course->image) : null,
                'total_subjects' => $subjects->count(),
                'subjects' => $subjects,
                'total_users' => $totalUsers,
                'overall_rating' => $overallRating,
                'total_review_count' => $totalReview,
                'is_purchased' => $isPurchased,
                'expiry_days_left' => $expiryDaysLeft,
            ];

            return ApiResponse::success('Course details retrieved successfully', $courseDetails);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve course details: ' . $th->getMessage(), null, 500);
        }
    }
    /**
     * Create a new course
     * 
     * Note: This method expects form-data (multipart/form-data) rather than JSON
     * as it handles file uploads for images
     */
    public function createCourse(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'semester' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('course_images', 'public');
                $validated['image'] = $imagePath;
            }

            $course = Courses::create($validated);

            return ApiResponse::success('Course created successfully', $course, 201);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to create course: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Update an existing course
     * 
     * Note: This method expects form-data (multipart/form-data) rather than JSON
     * as it handles file uploads for images
     */
    public function updateCourse(Request $request, $id)
    {
        try {
            // Debug the incoming request
            
            // Modified validation to be more lenient for debugging
            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'semester' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            \Log::info('Validated data:', $validated);
            
            // Find the course by ID
            $course = Courses::find($id);

            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($course->image) {
                    \Storage::disk('public')->delete($course->image);
                }

                $imagePath = $request->file('image')->store('course_images', 'public');
                $validated['image'] = $imagePath;
            }

            $course->update($validated);

            return ApiResponse::success('Course updated successfully', $course);
        } catch (\Throwable $th) {
            \Log::error('Update Course Error:', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return ApiResponse::serverError('Failed to update course: ' . $th->getMessage(), null, 500);
        }
    }



    // delete course
    public function deleteCourse($id)
    {
        try {
            $course = Courses::find($id);

            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            $course->delete();

            return ApiResponse::success('Course deleted successfully');
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to delete course: ' . $th->getMessage(), null, 500);
        }
    }

    public function addSemester(Request $request, $course_id)
    {
        try {
            $validated = $request->validate([
                'semester_number' => 'required|integer|min:1',
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $course = Courses::find($course_id);
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            $semester = $course->semesters()->create($validated);

            return ApiResponse::success('Semester added successfully', $semester);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to add semester: ' . $th->getMessage(), null, 500);
        }
    }

    public function updateSemester(Request $request, $course_id, $semester_id)
    {
        try {
            $validated = $request->validate([
                'semester_number' => 'required|integer|min:1',
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $course = Courses::find($course_id);
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            $semester = $course->semesters()->find($semester_id);
            if (!$semester) {
                return ApiResponse::clientError('Semester not found', null, 404);
            }

            $semester->update($validated);

            return ApiResponse::success('Semester updated successfully', $semester);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to update semester: ' . $th->getMessage(), null, 500);
        }
    }

    public function getSemesters($course_id)
    {
        try {
            $course = Courses::find($course_id);
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            $semesters = $course->semesters;

            return ApiResponse::success('Semesters retrieved successfully', $semesters);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve semesters: ' . $th->getMessage(), null, 500);
        }
    }

    public function approveCourseReview($review_id)
    {
        try {
            $review = \App\Models\CourseReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->is_approved = true;
            $review->save();

            return ApiResponse::success('Course review approved successfully', $review);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to approve course review: ' . $th->getMessage(), null, 500);
        }
    }

    public function rejectCourseReview($review_id)
    {
        try {
            $review = \App\Models\CourseReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->is_approved = false;
            $review->save();

            return ApiResponse::success('Course review rejected successfully', $review);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to reject course review: ' . $th->getMessage(), null, 500);
        }
    }
}
