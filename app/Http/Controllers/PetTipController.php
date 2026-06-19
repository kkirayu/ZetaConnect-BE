<?php

namespace App\Http\Controllers;

use App\Models\PetTip;
use Illuminate\Http\Request;

class PetTipController extends Controller
{
    public function index()
    {
        $tips = PetTip::all();
        return response()->json($tips);
    }

    public function show($id)
    {
        $tip = PetTip::find($id);
        if (!$tip) {
            return response()->json(['message' => 'Tip not found'], 404);
        }
        return response()->json($tip);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:100',
        ]);

        $tip = PetTip::create($request->all());
        return response()->json([
            'message' => 'Tip created successfully',
            'data' => $tip
        ], 201);
    }
}
