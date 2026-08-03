<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load('goals'),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($request, $user): void {
            $data = $request->validated();

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'language' => $data['language'],
                'onboarding_completed' => true,
            ]);

            if (array_key_exists('goals', $data)) {
                $user->goals()->sync($data['goals']);
            }
        });

        $user->load('goals');

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }
}