<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Chapters;
use App\Models\Subjects;
use App\Models\Courses;

class ChapterController extends Controller
{
    //

    public function getChaptersBySubjectId($subject_id)
    {
        try {
            // First, check if the subject exists
            $subject = Subjects::find($subject_id,'subject_id');
            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }
            
            $chapters = Chapters::where('subject_id', $subject_id)->get();
            
            if ($chapters->isEmpty()) {
                return ApiResponse::clientError('No chapters found for this subject', null, 404);
            }
            
            // Structure the response with subject details
            $result = [
                'subject_details' => [
                    'subject_id' => $subject->subject_id,
                    'subject_name' => $subject->subject_name,
                    'course_id' => $subject->course_id,
                    'semester' => $subject->semester,
                    'resource_link' => $subject->resource_link
                ],
                'chapters' => $chapters->map(function ($chapter) {
                    return [
                        'chapter_id' => $chapter->chapter_id,
                        'chapter_name' => $chapter->chapter_name,
                        'resource_link' => $chapter->resource_link,
                        'created_at' => $chapter->created_at,
                        'updated_at' => $chapter->updated_at
                    ];
                })
            ];
            
            return ApiResponse::success('Chapters retrieved successfully', $result);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve chapters: ' . $th->getMessage(), null, 500);
        }
    }
    public function createChapter(Request $request)
    {
        try {
            $validated = $request->validate([
                'chapter_name' => 'required|string|max:255',
                'subject_id' => 'required|integer|exists:subjects,subject_id',
                'resource_link' => 'nullable|string|max:255',
            ]);

            // Check if chapter already exists
            $existingChapter = Chapters::where('chapter_name', $validated['chapter_name'])
                ->where('subject_id', $validated['subject_id'])
                ->first();

            if ($existingChapter) {
                return ApiResponse::clientError('Chapter already exists', null, 409);
            }

            $chapter = Chapters::create($validated);

            return ApiResponse::success('Chapter created successfully', $chapter, 201);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to create chapter: ' . $th->getMessage(), null, 500);
        }
    }
    public function getChapterById($chapter_id)
    {
        try {
            $chapter = Chapters::find($chapter_id);
            if ($chapter) {
                return ApiResponse::success('Chapter retrieved successfully', $chapter);
            } else {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve chapter: ' . $th->getMessage(), null, 500);
        }
    }
    public function updateChapter(Request $request, $chapter_id)
    {
        try {
            $validated = $request->validate([
                'chapter_name' => 'required|string|max:255',
                'subject_id' => 'required|integer|exists:subjects,subject_id',
                'resource_link' => 'nullable|string|max:255',
            ]);

            $chapter = Chapters::find($chapter_id);
            if (!$chapter) {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }

            $chapter->update($validated);

            return ApiResponse::success('Chapter updated successfully', $chapter);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to update chapter: ' . $th->getMessage(), null, 500);
        }
    }
    public function deleteChapter($chapter_id)
    {
        try {
            $chapter = Chapters::find($chapter_id);
            if (!$chapter) {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }

            $chapter->delete();

            return ApiResponse::success('Chapter deleted successfully');
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to delete chapter: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Get all chapters for a specific course
     */
    public function getChaptersByCourseId($course_id)
    {
        try {
            // First, check if the course exists
            $course = Courses::find($course_id);
            if (!$course) {
                return ApiResponse::clientError('Course not found', null, 404);
            }

            // Get all subject IDs for this course
            $subject_ids = Subjects::where('course_id', $course_id)->pluck('subject_id')->toArray();
            
            if (empty($subject_ids)) {
                return ApiResponse::clientError('No subjects found for this course', null, 404);
            }

            // Get all chapters for these subjects
            $chapters = Chapters::whereIn('subject_id', $subject_ids)
                ->with('subject') // Load the subject relationship for better context
                ->get();

            if ($chapters->isEmpty()) {
                return ApiResponse::clientError('No chapters found for this course', null, 404);
            }
            
            // Structure the response by subject for better organization
            $result = [];
            foreach ($chapters as $chapter) {
                $subject_id = $chapter->subject_id;
                if (!isset($result[$subject_id])) {
                    $result[$subject_id] = [
                        'subject_name' => $chapter->subject->subject_name,
                        'chapters' => []
                    ];
                }
                $result[$subject_id]['chapters'][] = [
                    'chapter_id' => $chapter->chapter_id,
                    'chapter_name' => $chapter->chapter_name,
                    'resource_link' => $chapter->resource_link,
                    'created_at' => $chapter->created_at,
                    'updated_at' => $chapter->updated_at
                ];
            }
            
            return ApiResponse::success('Chapters retrieved successfully', $result);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve chapters: ' . $th->getMessage(), null, 500);
        }
    }

    // Add a method to get all chapters (missing in your controller)
    public function getChapters()
    {
        try {
            $chapters = Chapters::with('subject')->get();
            if ($chapters->isEmpty()) {
                return ApiResponse::clientError('No chapters found', null, 404);
            }
            return ApiResponse::success('All chapters retrieved successfully', $chapters);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            }
            return ApiResponse::serverError('Failed to retrieve chapters: ' . $th->getMessage(), null, 500);
        }
    }
}
