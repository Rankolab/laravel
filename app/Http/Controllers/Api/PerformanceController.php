<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\PerformanceMetric;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    /**
     * Retrieve performance metrics for a website or specific content.
     * Note: Actual metric fetching/updating from external APIs (e.g., Search Console, Analytics)
     * will be handled later via scheduled jobs or dedicated services.
     * This endpoint retrieves stored metrics.
     */
    public function getPerformance(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $contentId = $request->query("content_id");

        $query = PerformanceMetric::where("website_id", $website_id);

        if ($contentId) {
            // Validate content_id belongs to the website
            $contentExists = Content::where("content_id", $contentId)->where("website_id", $website_id)->exists();
            if (!$contentExists) {
                return response()->json(["error" => "Invalid content ID for this website"], 400);
            }
            $query->where("content_id", $contentId);
        }

        $metrics = $query->get();

        if ($metrics->isEmpty()) {
            return response()->json(["error" => "No performance data found"], 404);
        }

        // Format the response according to the API documentation
        $response = $metrics->map(function ($metric) {
            return [
                "performance_id" => $metric->performance_id,
                "website_id" => $metric->website_id,
                "content_id" => $metric->content_id,
                "keyword" => $metric->keyword,
                "ranking" => $metric->ranking,
                "clicks" => $metric->clicks,
                "impressions" => $metric->impressions,
                "affiliate_clicks" => $metric->affiliate_clicks,
                "affiliate_earnings" => number_format($metric->affiliate_earnings, 2, ".", ""), // Format decimal
                "indexed_status" => $metric->indexed_status,
                // "last_checked" is not in the example response, but available in DB
            ];
        });

        return response()->json($response, 200);
    }

    // Add methods for updating metrics if needed (likely done by background jobs)
}
