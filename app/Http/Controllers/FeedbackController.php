<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'type' => 'required|string|in:Pujian,Saran,Keluhan',
            'message' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'user_id' => Auth::guard('sanctum')->id(), // Can be null if not logged in
            'name' => $request->name,
            'phone' => $request->phone,
            'type' => $request->type,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ], 201);
    }

    public function index()
    {
        // For admin to view feedbacks, optionally included
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($feedbacks);
    }
}
