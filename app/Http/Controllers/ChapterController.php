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
                        'description' => $chapter->description,
                        'image' => $chapter->image ? url('storage/' . $chapter->image) : null,
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
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                'description' => $validated['description'] ?? null,
                'resource_link' => null, // Default to null
                'image' => null, // Default to null
            ];

            // Handle PDF file upload - storing publicly
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $fileName = time() . '_' . $pdfFile->getClientOriginalName();
                $pdfPath = $pdfFile->storeAs('chapters_pdf', $fileName, 'public');
                
                // Store the public URL for direct access
                $chapterData['resource_link'] = url('storage/' . $pdfPath);
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('chapter_images', 'public');
                $chapterData['image'] = $imagePath;
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
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max size
            ]);

            $chapter = Chapters::find($chapter_id);
            if (!$chapter) {
                return ApiResponse::clientError('Chapter not found', null, 404);
            }

            // Handle PDF file upload - store publicly
            if ($request->hasFile('pdf_file')) {
                // If there's an existing PDF file, delete it from public storage
                if ($chapter->resource_link) {
                    // Extract the filename from the URL
                    $path = parse_url($chapter->resource_link, PHP_URL_PATH);
                    $relativePath = str_replace('/storage/', '', $path);
                    Storage::disk('public')->delete($relativePath);
                }

                $pdfFile = $request->file('pdf_file');
                $fileName = time() . '_' . $pdfFile->getClientOriginalName();
                $pdfPath = $pdfFile->storeAs('chapters_pdf', $fileName, 'public');
                
                // Store the public URL
                $chapter->resource_link = url('storage/' . $pdfPath);
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // If there's an existing image, delete it from public storage
                if ($chapter->image) {
                    Storage::disk('public')->delete($chapter->image);
                }

                $imagePath = $request->file('image')->store('chapter_images', 'public');
                $chapter->image = $imagePath;
            }

            // Update other fields
            $chapter->chapter_name = $validated['chapter_name'];
            $chapter->subject_id = $validated['subject_id'];
            $chapter->description = $validated['description'] ?? $chapter->description;
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

            // If there's a PDF file in the resource_link, delete it from public storage
            if ($chapter->resource_link) {
                // Extract the filename from the URL
                $path = parse_url($chapter->resource_link, PHP_URL_PATH);
                $relativePath = str_replace('/storage/', '', $path);
                Storage::disk('public')->delete($relativePath);
            }

            // If there's an image file, delete it from public storage
            if ($chapter->image) {
                Storage::disk('public')->delete($chapter->image);
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
                    'description' => $chapter->description,
                    'image' => $chapter->image ? url('storage/' . $chapter->image) : null,
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
