<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Content; // Optional, if linking posts to content
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SocialMediaController extends Controller
{
    /**
     * Store (schedule) a new social media post for a website.
     * Note: Actual posting to social media platforms will be handled later.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "content_id" => "nullable|exists:content,content_id,website_id," . $website_id, // Ensure content belongs to the website
            "platform" => "required|string|in:facebook,twitter,linkedin,instagram,pinterest", // Example platforms
            "post_content" => "required|string|max:1000", // Max length depends on platform
            "status" => "required|string|in:draft,scheduled,posted,failed",
            "scheduled_at" => "nullable|date_format:Y-m-d H:i:s", // Validate datetime format
        ]);

        if ($validator->fails()) {
            // Check for specific platform error as per API doc example
            if ($validator->errors()->has("platform")) {
                 return response()->json(["error" => "Invalid platform specified"], 400);
            }
            return response()->json(["error" => "Invalid social media post parameters", "details" => $validator->errors()], 400);
        }

        // Ensure scheduled_at is provided if status is scheduled
        if ($request->input("status") === "scheduled" && !$request->input("scheduled_at")) {
            return response()->json(["error" => "Scheduled time is required for scheduled posts"], 400);
        }

        $socialMediaPost = SocialMediaPost::create([
            "website_id" => $website_id,
            "content_id" => $request->input("content_id"),
            "platform" => $request->input("platform"),
            "post_content" => $request->input("post_content"),
            "post_url" => null, // Will be set after successful posting
            "status" => $request->input("status"),
            "scheduled_at" => $request->input("scheduled_at") ? Carbon::parse($request->input("scheduled_at")) : null,
            "posted_at" => null,
        ]);

        // Placeholder: If status is 'scheduled', trigger a job for posting later.
        // If status is 'draft', just save it.
        // If status is 'posted' (immediate post), trigger posting job now (not implemented here).

        // Return response as per API documentation
        $response = [
            "post_id" => $socialMediaPost->post_id,
            "website_id" => $socialMediaPost->website_id,
            "platform" => $socialMediaPost->platform,
            "status" => $socialMediaPost->status,
        ];

        return response()->json($response, 201);
    }

    // Add other methods for managing social media posts if needed (e.g., update, delete, list)
}
