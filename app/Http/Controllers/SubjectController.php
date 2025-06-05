<?php

namespace App\Http\Controllers;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\Courses;
use App\Models\Chapters;
use App\Models\SubjectReview;
use App\Models\PhonePeTransactions;

class SubjectController extends Controller
{
    //
    public function getSubjects()
    {
        try {
            // $subjects = Subjects::all();
            $subjects = Subjects::withAvg('approvedReviews as average_rating', 'rating')
                ->withCount('approvedReviews as total_reviews')
                ->get();

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
    public function getSubjectById($id)
    {
        try {
            $subject = Subjects::find($id);

            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }

            // Fetch all chapters associated with the subject
            $chapters = Chapters::where('subject_id', $id)->get();

            $reviewStats = SubjectReview::where('subject_id', $id)
                ->where('is_approved', true)
                ->selectRaw('AVG(rating) as overall_rating, COUNT(*) as total_review')
                ->first();

            $overallRating = $reviewStats->overall_rating;
            $totalReview = $reviewStats->total_review;
                        
            $totalUsers = PhonePeTransactions::where('payment_type', 'subject')
            ->where('course_or_subject_id', $id)
            ->where('status','success')
            ->count();

            // Prepare detailed subject information
            $subjectDetails = [
                'subject_name' => $subject->subject_name,
                'course_id' => $subject->course_id,
                'image' => $subject->image ? url('storage/' . $subject->image) : null,
                'resource_link' => $subject->resource_link,
                'price' => $subject->price,
                'discount' => $subject->discount,
                'chapters' => $chapters,
                'totalUsers' => $totalUsers,
                'overall_rating' => $overallRating,
                'total_review_count' => $totalReview
            ];

            return ApiResponse::success('Subject details retrieved successfully', $subjectDetails);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve subject details: ' . $th->getMessage(), null, 500);
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

    public function createSubject(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject_name' => 'required|string|max:255',
                'course_id' => 'required|integer|exists:courses,course_id',
                'resource_link' => 'nullable|string|url',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            // Check if subject already exists for this course
            $existingSubject = Subjects::where('subject_name', $validated['subject_name'])
                ->where('course_id', $validated['course_id'])
                ->first();

            if ($existingSubject) {
                return ApiResponse::clientError('Subject already exists for this course', null, 409);
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('subject_images', 'public');
                $validated['image'] = $imagePath;
            }

            // Create the subject, handling missing 'resource_link' gracefully
            $subject = Subjects::create($validated);

            return ApiResponse::success('Subject created successfully', $subject, 201);
        } catch (\Throwable $e) {
            return ApiResponse::serverError('Failed to create subject: ' . $e->getMessage(), null, 500);
        }
    }

    public function updateSubject(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'subject_name' => 'required|string|max:255',
                'course_id' => 'required|integer|exists:courses,course_id',
                'resource_link' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image upload
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $subject = Subjects::find($id);
            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('subject_images', 'public');
                $validated['image'] = $imagePath;
            }

            $subject->update($validated);

            return ApiResponse::success('Subject updated successfully', $subject);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\QueryException) {
                return ApiResponse::serverError('Database error: ' . $th->getMessage(), null, 500);
            } elseif ($th instanceof \Illuminate\Validation\ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), null, 422);
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to update subject: ' . $th->getMessage(), null, 500);
            }
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
            } elseif ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('Subject not found', null, 404);
            } else {
                return ApiResponse::serverError('Failed to delete subject: ' . $th->getMessage(), null, 500);
            }          //throw $th;
        }
    }

    public function listAllSubjectsWithPricing()
    {
        try {
            $subjects = Subjects::all();

            if ($subjects->isEmpty()) {
                return ApiResponse::clientError('No subjects found', null, 404);
            }

            $detailedSubjects = $subjects->map(function ($subject) {
                return [
                    'subject_id' => $subject->subject_id,
                    'subject_name' => $subject->subject_name,
                    'course_id' => $subject->course_id,
                    'semester' => $subject->semester,
                    'price' => $subject->price,
                    'discount' => $subject->discount,
                ];
            });

            return ApiResponse::success('Subjects with pricing retrieved successfully', $detailedSubjects);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve subjects: ' . $th->getMessage(), null, 500);
        }
    }

    public function updatePricing(Request $request, $subject_id)
    {
        try {
            $validated = $request->validate([
                'price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
            ]);

            $subject = Subjects::find($subject_id);

            if (!$subject) {
                return ApiResponse::clientError('Subject not found', null, 404);
            }

            $subject->update($validated);

            return ApiResponse::success('Pricing updated successfully', $subject);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to update pricing: ' . $th->getMessage(), null, 500);
        }
    }

    public function approveSubjectReview($review_id)
    {
        try {
            $review = \App\Models\SubjectReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->is_approved = true;
            $review->save();

            return ApiResponse::success('Subject review approved successfully', $review);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to approve subject review: ' . $th->getMessage(), null, 500);
        }
    }

    public function rejectSubjectReview($review_id)
    {
        try {
            $review = \App\Models\SubjectReview::find($review_id);

            if (!$review) {
                return ApiResponse::clientError('Review not found', null, 404);
            }

            $review->is_approved = false;
            $review->save();

            return ApiResponse::success('Subject review rejected successfully', $review);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to reject subject review: ' . $th->getMessage(), null, 500);
        }
    }
}
