<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\CourseSemester;
use App\Models\Courses;

class CourseSemesterController extends Controller
{
    public function getPricing($course_id, $semester_id)
    {
        try {
            $semester = CourseSemester::where('course_id', $course_id)->find($semester_id);

            if (!$semester) {
                return ApiResponse::clientError('Semester not found', null, 404);
            }

            return ApiResponse::success('Pricing retrieved successfully', [
                'price' => $semester->price,
                'discount' => $semester->discount
            ]);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve pricing: ' . $th->getMessage(), null, 500);
        }
    }

    public function updatePricing(Request $request, $course_id, $semester_id)
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $semester = CourseSemester::where('course_id', $course_id)->find($semester_id);

            if (!$semester) {
                return ApiResponse::clientError('Semester not found', null, 404);
            }

            $semester->update($validated);

            return ApiResponse::success('Pricing updated successfully', $semester);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to update pricing: ' . $th->getMessage(), null, 500);
        }
    }

    public function listPricing($course_id)
    {
        try {
            $semesters = CourseSemester::where('course_id', $course_id)->get();

            if ($semesters->isEmpty()) {
                return ApiResponse::clientError('No semesters found for this course', null, 404);
            }

            return ApiResponse::success('Pricing list retrieved successfully', $semesters);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve pricing list: ' . $th->getMessage(), null, 500);
        }
    }

    public function listAllCoursesWithSemesters()
    {
        try {
            $courses = Courses::with('semesters')->get();

            if ($courses->isEmpty()) {
                return ApiResponse::clientError('No courses found', null, 404);
            }

            $detailedCourses = $courses->map(function ($course) {
                return [
                    'course_id' => $course->course_id,
                    'course_name' => $course->course_name,
                    'semesters' => $course->semesters->map(function ($semester) {
                        return [
                            'semester_number' => $semester->semester_number,
                            'price' => $semester->price,
                            'discount' => $semester->discount,
                        ];
                    }),
                ];
            });

            return ApiResponse::success('Courses with semesters retrieved successfully', $detailedCourses);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve courses: ' . $th->getMessage(), null, 500);
        }
    }

    public function listCoursesWithSemesterPricing()
    {
        try {
            $courses = Courses::with('semesters')->get();

            if ($courses->isEmpty()) {
                return ApiResponse::clientError('No courses found', null, 404);
            }

            $detailedCourses = $courses->flatMap(function ($course) {
                return $course->semesters->map(function ($semester) use ($course) {
                    return [
                        'course_id' => $course->course_id,
                        'course_name' => $course->course_name . ' - Semester ' . $semester->semester_number,
                        'semester_number' => $semester->semester_number,
                        'price' => $semester->price,
                        'discount' => $semester->discount,
                    ];
                });
            });

            return ApiResponse::success('Courses with semester-specific pricing retrieved successfully', $detailedCourses);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve courses: ' . $th->getMessage(), null, 500);
        }
    }

    public function updateSemesterPricing(Request $request, $course_id, $semester_id)
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $semester = CourseSemester::where('course_id', $course_id)->find($semester_id);

            if (!$semester) {
                return ApiResponse::clientError('Semester not found', null, 404);
            }

            $semester->update($validated);

            return ApiResponse::success('Semester pricing updated successfully', $semester);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to update semester pricing: ' . $th->getMessage(), null, 500);
        }
    }
}