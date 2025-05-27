<?php

use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PhonePeController;
 
// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);
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


// Review routes
Route::post('/reviews',[ReviewController::class, 'createReview']);
Route::get('/reviews/subject/{subject_id}', [ReviewController::class, 'getReviewsBySubjectId']);
Route::put('/reviews/{review_id}', [ReviewController::class, 'approveReview']);
Route::delete('/reviews/{review_id}', [ReviewController::class, 'deleteReview']);