<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\CourseReview;
use App\Models\PhonePeTransactions;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // get courses
    public function getCourses()
    {
        try {
            // $courses = Courses::all();
            $courses = Courses::withAvg('approvedReviews as average_rating', 'rating')->get();

            if ($courses->isEmpty()) {
                return ApiResponse::clientError('No courses found', null, 404);
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

            // Fetch total subjects for the course
            $subjects = Subjects::where('course_id', $id)->get();

            // Fetch total users who bought the course
           $totalUsers = PhonePeTransactions::where('payment_type', 'course')
            ->where('course_or_subject_id', $id)
            ->where('status','success')
            ->count();

            // Calculate overall rating for the course
            $overallRating = CourseReview::where('course_id', $id)->avg('rating');

            // Prepare detailed course information
            $courseDetails = [
                'course_name' => $course->course_name,
                'course_description' => $course->description,
                'price'=> $course->price,
                'semester' => $course->semester,
                'image' => $course->image ? url('storage/' . $course->image) : null,
                'total_subjects' => $subjects->count(),
                'subjects' => $subjects,
                'total_users' => $totalUsers,
                'overall_rating' => $overallRating,
            ];

            return ApiResponse::success('Course details retrieved successfully', $courseDetails);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve course details: ' . $th->getMessage(), null, 500);
        }
    }
    // create course

    public function createCourse(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'semester' => 'required|integer|min:1',
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

    // update course
    public function updateCourse(Request $request, $id)
    {
      
        try {

            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'semester' => 'required|integer|min:1',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
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
