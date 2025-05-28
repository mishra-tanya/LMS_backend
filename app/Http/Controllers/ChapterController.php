<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Chapters;
use App\Models\Subjects;
use App\Models\Courses;
use Illuminate\Support\Facades\Storage;

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
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max size
            ]);

            // Check if chapter already exists
            $existingChapter = Chapters::where('chapter_name', $validated['chapter_name'])
                ->where('subject_id', $validated['subject_id'])
                ->first();

            if ($existingChapter) {
                return ApiResponse::clientError('Chapter already exists', null, 409);
            }

            $chapterData = [
                'chapter_name' => $validated['chapter_name'],
                'subject_id' => $validated['subject_id'],
                'resource_link' => null, // Default to null
            ];

            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $fileName = time() . '_' . $pdfFile->getClientOriginalName();
                $pdfPath = $pdfFile->storeAs('chapters_pdf', $fileName, 'public');
                $chapterData['resource_link'] = asset('storage/' . $pdfPath);
            }

            $chapter = Chapters::create($chapterData);

            return ApiResponse::success('Chapter created successfully', $chapter, 201);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), $th->errors(), 422);
            } elseif ($th instanceof \Illuminate\Database\QueryException) {
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
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max size
            ]);

            $chapter = Chapters::find($chapter_id);
            if (!$chapter) {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }

            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                // If there's an existing PDF file, extract its path from the resource_link
                if ($chapter->resource_link) {
                    $currentPath = str_replace(asset('storage/'), '', $chapter->resource_link);
                    // Delete the old file if it exists
                    if (Storage::disk('public')->exists($currentPath)) {
                        Storage::disk('public')->delete($currentPath);
                    }
                }

                $pdfFile = $request->file('pdf_file');
                $fileName = time() . '_' . $pdfFile->getClientOriginalName();
                $pdfPath = $pdfFile->storeAs('chapters_pdf', $fileName, 'public');
                $chapter->resource_link = asset('storage/' . $pdfPath);
            }

            // Update other fields
            $chapter->chapter_name = $validated['chapter_name'];
            $chapter->subject_id = $validated['subject_id'];
            $chapter->save();

            return ApiResponse::success('Chapter updated successfully', $chapter);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), $th->errors(), 422);
            } elseif ($th instanceof \Illuminate\Database\QueryException) {
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

            // If there's a PDF file in the resource_link, delete it
            if ($chapter->resource_link) {
                $filePath = str_replace(asset('storage/'), '', $chapter->resource_link);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
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
