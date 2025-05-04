<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteMetric; // Import WebsiteMetric model
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // To get authenticated user

class WebsiteController extends Controller
{
    /**
     * Register a new website for the authenticated user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // user_id is not needed in request body, use authenticated user
            // 'user_id' => 'required|exists:users,user_id',
            'domain' => 'required|string|url|max:255|unique:websites,domain',
            'niche' => 'nullable|string|max:100',
            'website_type' => 'required|in:new,existing',
        ]);

        if ($validator->fails()) {
            // Provide specific error for unique domain constraint
            if ($validator->errors()->has('domain') && str_contains($validator->errors()->first('domain'), 'unique')) {
                return response()->json(['error' => 'Domain already registered'], 400);
            }
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user(); // Get the authenticated user

        $website = Website::create([
            'user_id' => $user->user_id,
            'domain' => $request->domain,
            'niche' => $request->niche,
            'website_type' => $request->website_type,
        ]);

        // Optionally, create a default metrics record upon website creation
        WebsiteMetric::create(['website_id' => $website->website_id]);

        // Return only the fields specified in the API documentation
        $response = $website->only(['website_id', 'domain', 'niche', 'website_type']);

        return response()->json($response, 201);
    }

    /**
     * Retrieve SEO and performance metrics for a specific website.
     * Note: This currently returns dummy data or data from the DB.
     * Actual metric fetching from external APIs will be implemented later.
     */
    public function getMetrics(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where('website_id', $website_id)->where('user_id', $user->user_id)->first();

        if (!$website) {
            return response()->json(['error' => 'Website not found or access denied'], 404);
        }

        // Fetch metrics from the database (assuming WebsiteMetric model and relationship exist)
        $metrics = $website->metrics; // Use the relationship defined in Website model

        if (!$metrics) {
            // Handle case where metrics record might not exist yet
            // Optionally create one here or return default values
            return response()->json(['error' => 'Metrics data not available yet'], 404);
        }

        // Format the response according to the API documentation
        $response = [
            'website_id' => (int)$metrics->website_id,
            'domain_authority' => (int)$metrics->domain_authority,
            'seo_score' => (int)$metrics->seo_score,
            'backlinks_count' => (int)$metrics->backlinks_count,
            'page_speed_score' => (int)$metrics->page_speed_score,
            'last_analyzed' => $metrics->last_analyzed ? Carbon::parse($metrics->last_analyzed)->toIso8601String() : null,
        ];

        return response()->json($response, 200);
    }

    // Add other methods for Website Design, Content Planning, etc. later
}
