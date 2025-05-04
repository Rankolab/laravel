<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\ContentPlan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ContentPlanController extends Controller
{
    /**
     * Store a new content plan for a website.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "keywords" => "nullable|array",
            "competitor_urls" => "nullable|array",
            "competitor_urls.*" => "nullable|url", // Validate each URL in the array
            "content_types" => "nullable|array",
            "volume" => "nullable|integer|min:1",
            "schedule" => "nullable|array",
            "schedule.frequency" => "nullable|string|in:daily,weekly,monthly", // Example validation
            "schedule.day" => "nullable|string",
        ]);

        if ($validator->fails()) {
            // Check for specific keyword error as per API doc example
            if ($validator->errors()->has("keywords")) {
                 return response()->json(["error" => "Invalid keywords"], 400);
            }
            return response()->json(["error" => "Invalid content plan parameters", "details" => $validator->errors()], 400);
        }

        // Use updateOrCreate if a website should only have one active plan, or create if multiple plans are allowed.
        // Assuming multiple plans are allowed per website for now.
        $contentPlan = ContentPlan::create([
            "website_id" => $website_id,
            "keywords" => $request->input("keywords"),
            "competitor_urls" => $request->input("competitor_urls"),
            "content_types" => $request->input("content_types"),
            "volume" => $request->input("volume", 1),
            "schedule" => $request->input("schedule"),
        ]);

        // Return response as per API documentation
        $response = [
            "plan_id" => $contentPlan->plan_id,
            "website_id" => $contentPlan->website_id,
            // Return only a subset of keywords for brevity, or adjust as needed
            "keywords" => array_slice($contentPlan->keywords ?? [], 0, 5), 
        ];

        return response()->json($response, 201);
    }

    // Add other methods for managing content plans if needed (e.g., update, delete, get)
}
