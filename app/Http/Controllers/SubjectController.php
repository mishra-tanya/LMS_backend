<?php

namespace App\Http\Controllers;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\Courses;     

class SubjectController extends Controller
{
    //
    public function getSubjects()  {
        try {
            $subjects = Subjects::all();
            if ($subjects->isEmpty()) {
                return ApiResponse::clientError('No subjects found', null, 404);
            }
            return ApiResponse::success('Subjects retrieved successfully', $subjects);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve subjects: ' . $th->getMessage(), null, 500);
        }
        
    }
    public function getSubjectById($id){
        try {
            
            $subject = Subjects::find($id, 'subject_id');
            if ($subject) {
                return ApiResponse::success('Subject retrieved successfully', $subject);
            } else {
                return ApiResponse::clientError('Subject not found', null, 404);
            }

        } catch (\Throwable $th) {
            //throw $th;
            if($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            if($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve subject: ' . $th->getMessage(), null, 500);
        }

    }

    public function getSubjectsByCourseId($course_id)
    {
        try {
            $subjects = Subjects::where('course_id', $course_id)->get();
            if ($subjects->isEmpty()) {
                return ApiResponse::clientError('No subjects found for this course', null, 404);
            }
            return ApiResponse::success('Subjects retrieved successfully', $subjects);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve subjects: ' . $th->getMessage(), null, 500);
        }
    }

    public function createSubject(Request $request){
        try {
            $validated = $request->validate([
                'subject_name' => 'required|string|max:255',
                'course_id' => 'required|integer|exists:courses,course_id',
                'resource_link' => 'nullable|string|url',
                'semester' => 'required|integer|min:1',
            ]);
            
            // Check if subject already exists for this course and semester
            $existingSubject = Subjects::where('subject_name', $validated['subject_name'])
                ->where('course_id', $validated['course_id'])
                ->where('semester', $validated['semester'])
                ->first();
                
            if ($existingSubject) {
                return ApiResponse::clientError('Subject already exists for this course and semester', null, 409);
            }
            
            // Create the subject, handling missing 'resource_link' gracefully
            $subject = Subjects::create([
                'subject_name' => $validated['subject_name'],
                'course_id' => $validated['course_id'],
                'resource_link' => $validated['resource_link'] ?? null, // Default to null if not provided
                'semester' => $validated['semester'],
            ]);
            
            return ApiResponse::success('Subject created successfully', $subject, 201);
        } catch (\Throwable $e) {
            return ApiResponse::serverError('Failed to create subject: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateSubject(Request $request, $id)
    {
        try {
            //code...
            $validated = $request->validate([
                'subject_name' => 'required|string|max:255',
                'course_id' => 'required|integer|exists:courses,course_id',
                'resource_link' => 'nullable|string|max:255',
                'semester' => 'required|integer|min:1',
            ]);
            $subject = Subjects::find($id);
            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            $subject->update($validated);
            return ApiResponse::success('Subject updated successfully', $subject);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            elseif($th instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            }
            elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            else{
                return ApiResponse::serverError('Failed to update subject: ' . $th->getMessage(), null, 500);
            }          //throw $th;
        }
    }

    public function deleteSubject($id)
    {
        try {
            //code...
            $subject = Subjects::find($id);
            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            $subject->delete();
            return ApiResponse::success('Subject deleted successfully');
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            else{
                return ApiResponse::serverError('Failed to delete subject: ' . $th->getMessage(), null, 500);
            }          //throw $th;
        }
    }
}
