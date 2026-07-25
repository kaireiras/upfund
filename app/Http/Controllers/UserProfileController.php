<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /**
     * Get User Profile
     * GET /api/profile
     */
    public function show(Request $request)
    {
        $user = $request->user()->load('kyc');

        return response()->json([
            'status' => 'success',
            'data'   => new UserProfileResource($user)
        ]);
    }

    /**
     * Update User Profile
     * PUT /api/profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'username'             => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email'                => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'bio'                  => 'nullable|string|max:1000',
            'avatar_url'           => 'nullable|url|max:1024',
            'bank_account_details' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully!',
            'data'    => new UserProfileResource($user->fresh('kyc'))
        ]);
    }
}