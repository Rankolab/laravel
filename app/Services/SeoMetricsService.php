<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\PagespeedInsights;
use Illuminate\Support\Facades\Http; // For simple HTTP calls if needed
use Illuminate\Support\Facades\Log;
use App\Models\WebsiteMetric;
use App\Models\Website;
use Carbon\Carbon;

class SeoMetricsService
{
    protected $googleClient;
    protected $apiKey; // Store API key

    public function __construct()
    {
        // Initialize Google Client - API Key needed for PageSpeed Insights
        // Search Console requires OAuth setup, which is more complex and needs user interaction
        $this->googleClient = new GoogleClient();
        // IMPORTANT: An API key needs to be configured in .env and loaded here
        $this->apiKey = env("GOOGLE_API_KEY"); 
        if ($this->apiKey) {
            $this->googleClient->setDeveloperKey($this->apiKey);
        } else {
            Log::warning("GOOGLE_API_KEY is not set. PageSpeed Insights API will not work.");
        }
        $this->googleClient->setApplicationName("Rankolab Backend");
        // No OAuth setup here, as it requires user flow. Search Console integration will need separate handling.
    }

    /**
     * Fetch and update PageSpeed Insights score for a website.
     * Uses a free, open-source API (Google PageSpeed Insights).
     */
    public function updatePageSpeedScore(Website $website)
    {
        // Check if the API key was loaded during construction
        if (!$this->apiKey) {
            Log::error("Cannot update PageSpeed score: GOOGLE_API_KEY is missing.");
            return false;
        }

        try {
            $pagespeedService = new PagespeedInsights($this->googleClient);
            $result = $pagespeedService->pagespeedapi->runpagespeed($website->domain, [
                "strategy" => "DESKTOP" // Or MOBILE, or both
            ]);

            // Extract the performance score (0-100)
            $score = $result->getLighthouseResult()->getCategories()->getPerformance()->getScore() * 100;

            // Update the WebsiteMetric record
            $metrics = $website->metrics()->firstOrCreate(["website_id" => $website->website_id]);
            $metrics->page_speed_score = round($score);
            $metrics->last_analyzed = Carbon::now(); // Update last analyzed time
            $metrics->save();

            Log::info("Updated PageSpeed score for website ID: " . $website->website_id . " to " . $metrics->page_speed_score);
            return true;

        } catch (\Exception $e) {
            Log::error("Error fetching PageSpeed Insights for website ID: " . $website->website_id . " - " . $e->getMessage());
            // Check for API key specific errors (though the initial check should catch missing key)
            if (str_contains($e->getMessage(), 'API key not valid')) {
                 Log::error("Google API Key may be invalid or missing required permissions.");
            }
            return false;
        }
    }

    /**
     * Placeholder for updating metrics from Google Search Console.
     * Requires OAuth 2.0 setup and user consent.
     * This would typically run as a scheduled job.
     * Uses a free API (Google Search Console API).
     */
    public function updateSearchConsoleMetrics(Website $website)
    {
        // 1. Check if user has authorized Google Search Console access (OAuth token storage/refresh needed).
        // 2. Initialize Google Client with OAuth credentials.
        // 3. Create Search Console service instance.
        // 4. Query the API for performance data (clicks, impressions, ranking for keywords).
        // 5. Update PerformanceMetric records in the database.
        Log::info("Placeholder: updateSearchConsoleMetrics called for website ID: " . $website->website_id . ". Requires OAuth setup.");
        // Example: Update last_analyzed time even if no data is fetched yet
        $metrics = $website->metrics()->firstOrCreate(["website_id" => $website->website_id]);
        $metrics->last_analyzed = Carbon::now();
        $metrics->save();
        return true; // Return true for now
    }

    /**
     * Placeholder for updating Domain Authority and Backlinks.
     * Note: Reliable DA and backlink data often requires paid APIs (e.g., Moz, Ahrefs, SEMrush).
     * Free/open-source options are limited and less accurate.
     * We will leave these as placeholders or use dummy data for now.
     */
    public function updateDomainAuthorityAndBacklinks(Website $website)
    {
        Log::info("Placeholder: updateDomainAuthorityAndBacklinks called for website ID: " . $website->website_id . ". Requires external (likely paid) API or manual input.");
        // Example: Update last_analyzed time
        $metrics = $website->metrics()->firstOrCreate(["website_id" => $website->website_id]);
        // Optionally set dummy data if needed for testing
        // $metrics->domain_authority = rand(10, 50); // Dummy data
        // $metrics->backlinks_count = rand(100, 10000); // Dummy data
        $metrics->last_analyzed = Carbon::now();
        $metrics->save();
        return true; // Return true for now
    }

    /**
     * Placeholder for updating SEO Score.
     * This is often a composite score derived from various metrics.
     * Calculation logic needs to be defined.
     */
     public function updateSeoScore(Website $website)
     {
         Log::info("Updating SEO Score for website ID: " . $website->website_id);
         $metrics = $website->metrics()->firstOrCreate(["website_id" => $website->website_id]);
         
         // Basic SEO Score Calculation: Start with PageSpeed score
         // As other metrics (DA, Backlinks, Search Console) are placeholders or require external setup,
         // we will base the initial score primarily on the available PageSpeed score.
         // This logic can be expanded later when more metrics are reliably available.
         $score = 0;
         if (isset($metrics->page_speed_score) && is_numeric($metrics->page_speed_score)) {
             // Let's use PageSpeed as the main component for now (e.g., 100% weight)
             // We can normalize it if needed, but for simplicity, let's use it directly.
             $score = $metrics->page_speed_score;
         } else {
             Log::warning("PageSpeed score not available for website ID: " . $website->website_id . ". SEO score calculation might be inaccurate.");
             // Optionally, fetch PageSpeed score if not available
             // $this->updatePageSpeedScore($website);
             // $metrics->refresh(); // Refresh metrics after potential update
             // $score = $metrics->page_speed_score ?? 0;
         }

         // Ensure score is within 0-100 range
         $metrics->seo_score = max(0, min(100, round($score)));
         $metrics->last_analyzed = Carbon::now(); // Update last analyzed time
         $metrics->save();

         Log::info("Updated SEO score for website ID: " . $website->website_id . " to " . $metrics->seo_score);
         return true;
     }

    /**
     * Trigger updates for all relevant SEO metrics for a website.
     * This would typically be called by a scheduled job.
     */
    public function updateAllMetrics(Website $website)
    {
        Log::info("Starting metric update for website ID: " . $website->website_id);
        $this->updatePageSpeedScore($website);
        $this->updateSearchConsoleMetrics($website); // Placeholder
        $this->updateDomainAuthorityAndBacklinks($website); // Placeholder
        $this->updateSeoScore($website); // Placeholder
        Log::info("Finished metric update for website ID: " . $website->website_id);
    }
}

