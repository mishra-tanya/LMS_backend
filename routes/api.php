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
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\FeaturedCourseOrSubjectController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\StudentBlockController;
// public routes 

// Auth routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

// email verification routes
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// password forgot routes
Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::get('/password/reset/{token}', function ($token) {
    return response()->json(['token' => $token]);
})->name('password.reset');

// phonepe payments (callback from phonepe)
Route::get('phonepe-callback', [PhonePeController::class, 'callback'])->name('phonepe.callback');

// featured courses 
Route::get('/featuredCourseOrSubject', [FeaturedCourseOrSubjectController::class, 'getFeaturedCourseOrSubject']);

// Course routes
Route::get('/courses', [CourseController::class, 'getCourses']);
Route::get('/courses/{id}', [CourseController::class, 'getcourseById']);

// Subject routes
Route::get('/subjects', [SubjectController::class, 'getSubjects']);
Route::get('/subjects/{id}', [SubjectController::class, 'getSubjectById']);
Route::get('/subjects/course/{course_id}', [SubjectController::class, 'getSubjectsByCourseId']);

// Chapter routes
Route::get('/chapters', [ChapterController::class, 'getChapters']);
Route::get('/chapters/subject/{subject_id}', [ChapterController::class, 'getChaptersBySubjectId']);
Route::get('/chapters/course/{course_id}', [ChapterController::class, 'getChaptersByCourseId']); 
Route::get('/chapters/{id}', [ChapterController::class, 'getChapterById']);

// course Review routes
Route::get('/coursereviews', [CourseReviewController::class, 'getCourseReviews']);
Route::get('/coursereviews/course/{course_id}', [CourseReviewController::class, 'getReviewsByCourseId']);
Route::get('/coursereviews/approved', [CourseReviewController::class, 'getApprovedCourseReviews']);

// subject Review routes
Route::get('/subjectreviews', [SubjectReviewController::class, 'getSubjectReviews']);
Route::get('/subjectreviews/subject/{subject_id}', [SubjectReviewController::class, 'getReviewsBySubjectId']);
Route::get('/subjectreviews/approved', [SubjectReviewController::class, 'getApprovedSubjectReviews']);

// coupon
Route::post('/apply', [CouponController::class, 'apply']);        
Route::post('/validate-coupon', [CouponController::class, 'validateCoupon']);        

// user protected rotues
Route::middleware(['auth:api', 'check.jwt'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/phonepe-initiate', [PhonePeController::class, 'initiate'])->middleware('throttle:5,1');;

    Route::get('/user-purchase-history', [PurchaseController::class, 'getUserPurchaseHistory']);
    Route::post('/free-course-or-subject', [PurchaseController::class, 'initiateFreeCourseSubject']);
    Route::post('/coursereviews', [CourseReviewController::class, 'createCourseReview']);
    Route::post('/subjectreviews', [SubjectReviewController::class, 'createSubjectReview']);
});

// student Routes 
Route::middleware(['auth:api','check.jwt'])->prefix('student')->group(function () {
    Route::get('/profile', [StudentController::class, 'getProfile']);
    Route::put('/profile', [StudentController::class, 'updateProfile']);
    Route::post('/change-password', [StudentController::class, 'changePassword']);
    Route::get('/purchased-courses', [StudentController::class, 'getPurchasedCourses']);
    Route::get('/payment-history', [PurchaseController::class, 'getUserPurchaseHistory']);
    Route::get('/my-reviews', [StudentController::class, 'getMyReviews']);    
});
    Route::get('/delete-coupon/{id}', [CouponController::class, 'deleteCoupon']);    


    Route::get('/students', [StudentBlockController::class, 'index']);
    Route::post('/students/{id}/block', [StudentBlockController::class, 'block']);
    Route::post('/students/{id}/unblock', [StudentBlockController::class, 'unblock']);
    Route::get('/students/{id}', [StudentBlockController::class, 'show']);

// admin routes
Route::middleware(['admin'])->group(function () {
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/create-coupon', [CouponController::class, 'create']);       
    Route::put('/update/{id}', [CouponController::class, 'update']);   

    Route::put('/coursereviews/{review_id}/approve', [CourseReviewController::class, 'approveCourseReview']);
    
    // Purchase history
    Route::get('/purchase-history', [PurchaseController::class, 'getAllPurchaseHistory']);

    // statistics
    Route::get('/statistics', [StatisticsController::class, 'getStatistics']);

    // courses
    Route::post('/courses', [CourseController::class, 'createCourse']);
    Route::put('/courses/{id}', [CourseController::class, 'updateCourse']);
    Route::delete('/courses/{id}', [CourseController::class, 'deleteCourse']);

    // subjects
    Route::post('/subjects', [SubjectController::class, 'createSubject']);
    Route::put('/subjects/{id}', [SubjectController::class, 'updateSubject']);
    Route::delete('/subjects/{id}', [SubjectController::class, 'deleteSubject']);

    // chapter
    Route::post('/chapters', [ChapterController::class, 'createChapter']);
    Route::put('/chapters/{id}', [ChapterController::class, 'updateChapter']);
    Route::delete('/chapters/{id}', [ChapterController::class, 'deleteChapter']);

    // course reviews (update delete review)
    Route::put('/coursereviews/{review_id}', [CourseReviewController::class, 'approveCourseReview']);
    Route::delete('/coursereviews/{review_id}', [CourseReviewController::class, 'deleteCourseReview']);

    // course review approve
    Route::put('/coursereviews/{review_id}/approve', [CourseReviewController::class, 'approveCourseReview']);

    // subject reviews (update delete review)
    Route::put('/subjectreviews/{review_id}', [SubjectReviewController::class, 'approveSubjectReview']);
    Route::delete('/subjectreviews/{review_id}', [SubjectReviewController::class, 'deleteSubjectReview']);
    
    // course review approve
    Route::put('/subjectreviews/{review_id}/approve', [SubjectReviewController::class, 'approveSubjectReview']);
    
});
