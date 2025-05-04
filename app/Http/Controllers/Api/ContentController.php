<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Content;
use App\Models\ContentPlan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContentController extends Controller
{
    /**
     * Store (generate) new content for a website.
     * Note: Actual content generation using external APIs will be handled in a separate service/job later.
     * This endpoint currently creates a placeholder/draft record.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "plan_id" => "nullable|exists:content_plans,plan_id,website_id," . $website_id, // Ensure plan belongs to the website
            "title" => "required|string|max:255",
            "word_count" => "required|integer|min:100", // Example minimum
            "keywords" => "nullable|array",
            "content_type" => "nullable|string|max:50", // e.g., blog, article
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => "Invalid content parameters", "details" => $validator->errors()], 400);
        }

        // Placeholder: In a real scenario, trigger a job to generate content using external APIs
        // For now, create a draft record with placeholder body
        $generatedBody = "Placeholder content for ".$request->input("title")." - Generation pending.";
        $generatedImageUrl = "https://via.placeholder.com/800x400.png?text=Placeholder+Image"; // Placeholder image

        $content = Content::create([
            "website_id" => $website_id,
            "plan_id" => $request->input("plan_id"),
            "title" => $request->input("title"),
            "body" => $generatedBody, // Placeholder
            "word_count" => $request->input("word_count"),
            "featured_image_url" => $generatedImageUrl, // Placeholder
            "images" => [$generatedImageUrl], // Placeholder
            "internal_links" => [], // Placeholder
            "external_links" => [], // Placeholder
            "affiliate_links" => [], // Placeholder
            "keywords" => $request->input("keywords"),
            "status" => "draft", // Default status
            "published_at" => null,
        ]);

        // Return response as per API documentation
        $response = [
            "content_id" => $content->content_id,
            "website_id" => $content->website_id,
            "title" => $content->title,
            "status" => $content->status,
            "featured_image_url" => $content->featured_image_url,
        ];

        return response()->json($response, 201);
    }

    /**
     * Publish a draft content piece.
     * Note: Actual submission to Google Search Console will be handled later.
     */
    public function publish(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "content_id" => "required|exists:content,content_id,website_id," . $website_id, // Ensure content belongs to the website
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => "Invalid content ID", "details" => $validator->errors()], 400);
        }

        $content = Content::find($request->input("content_id"));

        if (!$content) {
             // Should be caught by exists rule, but for robustness
            return response()->json(["error" => "Content not found"], 404);
        }

        if ($content->status !== "draft") {
            return response()->json(["error" => "Content is not in draft status"], 400);
        }

        // Update status and published_at timestamp
        $content->status = "published";
        $content->published_at = Carbon::now();
        $content->save();

        // Placeholder: Trigger job/event for Google Search Console submission

        // Return response as per API documentation
        $response = [
            "content_id" => $content->content_id,
            "status" => $content->status,
            "published_at" => $content->published_at->toIso8601String(),
        ];

        return response()->json($response, 200);
    }

    // Add other methods for managing content if needed (e.g., update, delete, get)
}
