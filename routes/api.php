<?php

use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\PhonePeController;
use App\Http\Controllers\SubjectReviewController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\StudentController;

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::get('/password/reset/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

// user protected rotues
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// phonepe payments
Route::post('/phonepe-initiate', [PhonePeController::class, 'initiate']);
Route::post('/phonepe-callback', [PhonePeController::class, 'callback'])->name('phonepe.callback');

// Course routes
Route::get('/courses', [CourseController::class, 'getCourses']);
Route::get('/courses/{id}', [CourseController::class, 'getcourseById']);
Route::post('/courses', [CourseController::class, 'createCourse']);
Route::put('/courses/{id}', [CourseController::class, 'updateCourse']);
Route::delete('/courses/{id}', [CourseController::class, 'deleteCourse']);


// Subject routes
Route::get('/subjects', [SubjectController::class, 'getSubjects']);
Route::get('/subjects/{id}', [SubjectController::class, 'getSubjectById']);
Route::get('/subjects/course/{course_id}', [SubjectController::class, 'getSubjectsByCourseId']);
Route::post('/subjects', [SubjectController::class, 'createSubject']);
Route::put('/subjects/{id}', [SubjectController::class, 'updateSubject']);
Route::delete('/subjects/{id}', [SubjectController::class, 'deleteSubject']);


// Chapter routes
Route::get('/chapters', [ChapterController::class, 'getChapters']);
Route::get('/chapters/subject/{subject_id}', [ChapterController::class, 'getChaptersBySubjectId']);
Route::get('/chapters/course/{course_id}', [ChapterController::class, 'getChaptersByCourseId']); // New route
Route::get('/chapters/{id}', [ChapterController::class, 'getChapterById']);
Route::post('/chapters', [ChapterController::class, 'createChapter']);
Route::put('/chapters/{id}', [ChapterController::class, 'updateChapter']);
Route::delete('/chapters/{id}', [ChapterController::class, 'deleteChapter']);



// course Review routes
Route::post('/coursereviews', [CourseReviewController::class, 'createCourseReview']);
Route::get('/coursereviews', [CourseReviewController::class, 'getCourseReviews']);
Route::get('/coursereviews/course/{course_id}', [CourseReviewController::class, 'getReviewsByCourseId']);
Route::put('/coursereviews/{review_id}', [CourseReviewController::class, 'approveCourseReview']);
Route::delete('/coursereviews/{review_id}', [CourseReviewController::class, 'deleteCourseReview']);
Route::get('/coursereviews/approved', [CourseReviewController::class, 'getApprovedCourseReviews']);
Route::put('/coursereviews/{review_id}/approve', [CourseReviewController::class, 'approveCourseReview']);

// subject Review routes
Route::post('/subjectreviews', [SubjectReviewController::class, 'createSubjectReview']);
Route::get('/subjectreviews', [SubjectReviewController::class, 'getSubjectReviews']);
Route::get('/subjectreviews/subject/{subject_id}', [SubjectReviewController::class, 'getReviewsBySubjectId']);
Route::put('/subjectreviews/{review_id}', [SubjectReviewController::class, 'approveSubjectReview']);
Route::delete('/coursereviews/{review_id}', [SubjectReviewController::class, 'deleteSubjectReview']);
Route::get('/subjectreviews/approved', [SubjectReviewController::class, 'getApprovedSubjectReviews']);
Route::put('/subjectreviews/{review_id}/approve', [SubjectReviewController::class, 'approveSubjectReview']);


// student Routes 
Route::middleware('auth:api')->prefix('student')->group(function () {
    Route::get('/profile', [StudentController::class, 'getProfile']);
    Route::put('/profile', [StudentController::class, 'updateProfile']);
    Route::post('/change-password', [StudentController::class, 'changePassword']);
    Route::get('/purchased-courses', [StudentController::class, 'getPurchasedCourses']);
    Route::get('/payment-history', [StudentController::class, 'getPaymentHistory']);
    Route::get('/my-reviews', [StudentController::class, 'getMyReviews']);    
});

