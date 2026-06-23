<?php

namespace App\Http\Controllers;

use App\Services\PostcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostcodeController extends Controller
{
    public function lookup(Request $request, PostcodeService $service): JsonResponse
    {
        $request->validate([
            'postcode' => ['required', 'string', 'max:10'],
        ]);

        if (! PostcodeService::isValidUkPostcode($request->postcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid UK postcode format.',
            ], 422);
        }

        $result = $service->lookup($request->postcode);

        return response()->json($result);
    }
}
