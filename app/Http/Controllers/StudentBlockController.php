<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Block;
use App\Models\PhonePeTransactions;

class StudentBlockController extends Controller
{
    // Fetch all students with block info
    public function index()
    {
        $students = User::select('id', 'name', 'email', 'created_at')
        ->with(['block:id,user_id,reason,blocked_date'])
        ->orderBy('name')
        ->get()
        ->map(function ($student) {
            // Add status
            $student->status = $student->block ? 'blocked' : 'active';

            // Add blocked date if exists
            $student->blocked_date = $student->block ? $student->block->blocked_date : null;

            // Add total purchased courses
            $student->total_purchased_courses = PhonePeTransactions::where('user_id', $student->id)
                ->where('status', 'success')
                ->count();

            return $student;
        });

         return ApiResponse::success('Students fetched successfully', $students);
    }
    
    // Block a student
    public function block(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $student = User::find($id);
        if (!$student) {
            return ApiResponse::clientError('Student not found', null, 404);
        }

        if (Block::where('user_id', $id)->exists()) {
            return ApiResponse::clientError('Student is already blocked', null, 400);
        }

        Block::create([
            'user_id' => $id,
            'reason' => $request->reason,
            'blocked_date' => now()->format('Y-m-d')
        ]);

        return ApiResponse::success('Student blocked successfully', [
            'student' => $student,
            'block' => Block::where('user_id', $id)->first()
        ]);
    }

    // Unblock a student
    public function unblock($id)
    {
        $student = User::find($id);
        if (!$student) {
            return ApiResponse::clientError('Student not found', null, 404);
        }

        Block::where('user_id', $id)->delete();

        return ApiResponse::success('Student unblocked successfully', $student);
    }

    // Get student details
    public function show($id)
    {
        try {
            $student = User::with('block')->find($id);

            if (!$student) {
                return ApiResponse::clientError('Student not found', null, 404);
            }

            $student->status = $student->block ? 'blocked' : 'active';
            $student->account_created_at = $student->created_at;

            $purchasedCount = PhonePeTransactions::where('user_id', $student->id)
                ->where('status', 'success')
                ->count();

            $student->total_purchased_courses = $purchasedCount;

            return ApiResponse::success('Student details fetched successfully', $student);

        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to fetch student details: ' . $th->getMessage(), null, 500);
        }
    }

}