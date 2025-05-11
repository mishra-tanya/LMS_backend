<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Courses;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class CourseController extends Controller
{
    // get courses
    public function getCourses()
    {
        try {
            $courses = Courses::all();
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
            $course = Courses::find($id, 'course_id');
            if ($course) {
                return ApiResponse::success('Course retrieved successfully', $course);
            } else {
                return ApiResponse::clientError('Course not found', null, 404);
            }
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Course not found', null, 404);
            }
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve course: ' . $th->getMessage(), null, 500);
        }
    }
    // create course

    public function createCourse(Request $request)
    {
        try {
            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'total_semester' => 'required|integer|min:1',
            ]);

            // Check if course already exists
            $existingCourse = Courses::where('course_name', $request->course_name)->first();
            if ($existingCourse) {
                return ApiResponse::clientError('Course already exists', null, 409);
            }
            $course = Courses::create([
                'course_name' => $request->course_name,
                'total_semester' => $request->total_semester,
            ]);
            return ApiResponse::success('Course created successfully', $course, 201);
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $e->getMessage(), null, 422);
            }
            if ($e instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $e->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to create course: ' . $e->getMessage(), null, 500);
        }
    }
    // update course
    public function updateCourse(Request $request, $id)
    {
        try {


            $course = Courses::find($id, 'course_id');
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            $validated = $request->validate([
                'course_name' => 'required|string|max:255',
                'total_semester' => 'required|integer|min:1',
            ]);
            $course->update($validated);
            return ApiResponse::success('Course updated successfully', $course);

        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            }
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Course not found', null, 404);
            }
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to update course: ' . $th->getMessage(), null, 500);
        }
    }
    // delete course
    public function deleteCourse($id)
    {
        try {

            $course = Courses::find($id, 'course_id');
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }
            $course->delete();
            return ApiResponse::success('Course deleted successfully');
            //code...
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Course not found', null, 404);
            }
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to delete course: ' . $th->getMessage(), null, 500);
            //throw $th;
        }
    }
}
