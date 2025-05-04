<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\RssFeed;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RssFeedController extends Controller
{
    /**
     * Store a new RSS feed configuration for a website.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "feed_url" => "required|url|max:255",
            "feed_name" => "required|string|max:100",
            "quantity" => "nullable|integer|min:1",
            "word_count" => "nullable|integer|min:50", // Example minimum
            "schedule" => "nullable|array",
            "schedule.frequency" => "nullable|string|in:daily,weekly,monthly",
            "schedule.time" => "nullable|date_format:H:i", // Validate time format HH:MM
            "is_active" => "nullable|boolean", // Added based on migration
        ]);

        if ($validator->fails()) {
            // Check for specific feed URL error as per API doc example
            if ($validator->errors()->has("feed_url")) {
                 return response()->json(["error" => "Invalid feed URL"], 400);
            }
            return response()->json(["error" => "Invalid RSS feed parameters", "details" => $validator->errors()], 400);
        }

        // Assuming multiple feeds are allowed per website
        $rssFeed = RssFeed::create([
            "website_id" => $website_id,
            "feed_url" => $request->input("feed_url"),
            "feed_name" => $request->input("feed_name"),
            "quantity" => $request->input("quantity", 1),
            "word_count" => $request->input("word_count", 1000),
            "schedule" => $request->input("schedule"),
            "is_active" => $request->input("is_active", true),
        ]);

        // Return response as per API documentation
        $response = [
            "feed_id" => $rssFeed->feed_id,
            "website_id" => $rssFeed->website_id,
            "feed_name" => $rssFeed->feed_name,
        ];

        return response()->json($response, 201);
    }

    // Add other methods for managing RSS feeds if needed (e.g., update, delete, list)
}
