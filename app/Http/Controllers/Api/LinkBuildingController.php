<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\LinkBuilding;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LinkBuildingController extends Controller
{
    /**
     * Store a new link building record for a website.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "target_url" => "required|url|max:255",
            "source_url" => "required|url|max:255",
            "anchor_text" => "nullable|string|max:255",
            "status" => "required|string|in:pending,acquired,rejected", // Example statuses
            "link_type" => "nullable|string|in:guest_post,directory,resource_page,other", // Example types
            "acquired_date" => "nullable|date_format:Y-m-d", // Validate date format YYYY-MM-DD
        ]);

        if ($validator->fails()) {
            // Check for specific URL error as per API doc example
            if ($validator->errors()->has("target_url") || $validator->errors()->has("source_url")) {
                 return response()->json(["error" => "Invalid URL provided"], 400);
            }
            return response()->json(["error" => "Invalid link building parameters", "details" => $validator->errors()], 400);
        }

        $linkBuilding = LinkBuilding::create([
            "website_id" => $website_id,
            "target_url" => $request->input("target_url"),
            "source_url" => $request->input("source_url"),
            "anchor_text" => $request->input("anchor_text"),
            "status" => $request->input("status"),
            "link_type" => $request->input("link_type"),
            "acquired_date" => $request->input("acquired_date") ? Carbon::parse($request->input("acquired_date")) : null,
        ]);

        // Return response as per API documentation
        $response = [
            "link_id" => $linkBuilding->link_id,
            "website_id" => $linkBuilding->website_id,
            "target_url" => $linkBuilding->target_url,
            "status" => $linkBuilding->status,
        ];

        return response()->json($response, 201);
    }

    // Add other methods for managing link building records if needed (e.g., update, delete, list)
}
