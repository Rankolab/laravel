<?php

namespace App\Services;

use App\Models\ContentPlan;
use App\Models\Content;
use App\Models\Website;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ContentGenerationService
{
    protected $apyhubApiKey;
    protected $corticalApiKey;

    public function __construct()
    {
        $this->apyhubApiKey = env("APYHUB_API_KEY");
        $this->corticalApiKey = env("CORTICAL_API_KEY");

        if (!$this->apyhubApiKey) {
            Log::warning("ContentGenerationService: APYHUB_API_KEY is not set. Summarization features will not work.");
        }
        if (!$this->corticalApiKey) {
            Log::warning("ContentGenerationService: CORTICAL_API_KEY is not set. Keyword extraction features will not work.");
        }
    }

    /**
     * Summarize text using ApyHub API.
     *
     * @param string $text The text to summarize.
     * @param string $length ("short", "medium", "long")
     * @return string|null The summary or null on failure.
     */
    private function summarizeTextWithApyHub(string $text, string $length = "medium"): ?string
    {
        if (!$this->apyhubApiKey) {
            Log::error("Cannot summarize text: APYHUB_API_KEY is missing.");
            return null;
        }

        // ApyHub has separate endpoints for text and URL summarization. Assuming text input here.
        // Need to find the correct endpoint for direct text summarization if available, or use URL if required.
        // Let's assume a hypothetical text endpoint for now, or adapt if only URL is available.
        // Based on ApyHub docs, they have /ai/summarize-text

        try {
            $response = Http::withHeaders([
                "apy-token" => $this->apyhubApiKey, // Or "Authorization" => "Bearer " . $this->apyhubApiKey
                "Content-Type" => "application/json",
            ])->post("https://api.apyhub.com/ai/summarize-text", [
                "text" => $text,
                "summary_length" => $length,
            ]);

            if ($response->successful() && isset($response->json()["data"]["summary"])) {
                Log::info("Successfully summarized text using ApyHub.");
                return $response->json()["data"]["summary"];
            } else {
                Log::error("ApyHub summarization failed. Status: " . $response->status() . " Body: " . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Exception during ApyHub summarization: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract keywords using Cortical.io API.
     *
     * @param string $text The text to extract keywords from.
     * @param int $limit Max number of keywords.
     * @return array|null List of keywords or null on failure.
     */
    private function extractKeywordsWithCortical(string $text, int $limit = 10): ?array
    {
        if (!$this->corticalApiKey) {
            Log::error("Cannot extract keywords: CORTICAL_API_KEY is missing.");
            return null;
        }

        try {
            // Cortical.io uses POST /nlp/keywords
            // Authentication is likely Bearer token in Authorization header
            $response = Http::withHeaders([
                "Authorization" => "Bearer " . $this->corticalApiKey,
                "Content-Type" => "application/json",
            ])->post("https://api.cortical.io/rest/text/keywords", [ // Assuming this is the correct endpoint path
                "text" => $text,
                // Need to confirm parameter names, assuming 'limit' based on docs
                // "limit" => $limit, // Parameter might be different, e.g., 'max_keywords'
                // Need to specify the 'retina_name' (language model)
                "retina_name" => "en_associative" // Example, check available retinas
            ]);

            // Cortical response format needs checking. Assuming it returns a list of keywords.
            if ($response->successful() && is_array($response->json())) { // Adjust based on actual response structure
                Log::info("Successfully extracted keywords using Cortical.io.");
                // Assuming the response is directly the array of keywords
                return array_slice($response->json(), 0, $limit); // Limit keywords if API doesn't support it directly
            } else {
                Log::error("Cortical.io keyword extraction failed. Status: " . $response->status() . " Body: " . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Exception during Cortical.io keyword extraction: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate content based on a content plan, using AI tools if available.
     *
     * @param ContentPlan $plan The content plan to generate content for.
     * @return Content|null The generated content model instance, or null on failure.
     */
    public function generateContent(ContentPlan $plan): ?Content
    {
        Log::info("Generating content for plan ID: " . $plan->content_plan_id . " - Title: " . $plan->topic);

        try {
            $title = $plan->topic ?? 'Generated Content';
            $initialKeywords = $plan->keywords ?? [];
            $targetAudience = $plan->target_audience ?? '';

            // Placeholder: Generate a basic outline or intro based on title/keywords
            $baseText = "Introduction to {$title}. Key aspects include: " . implode(', ', $initialKeywords) . ". This content is targeted towards {$targetAudience}.";

            // Attempt to summarize the base text (as an example of using the AI tool)
            $summary = $this->summarizeTextWithApyHub($baseText, 'short');

            // Attempt to extract keywords from the base text
            $extractedKeywords = $this->extractKeywordsWithCortical($baseText);
            $finalKeywords = $extractedKeywords ? array_unique(array_merge($initialKeywords, $extractedKeywords)) : $initialKeywords;

            // Construct the body - use summary if available, otherwise use base text
            $body = $summary ? "Summary: {$summary}\n\n{$baseText}" : $baseText;
            $body .= "\n\nKeywords: [" . implode(', ', $finalKeywords) . "]";
            $body .= "\n\n(Note: This content was auto-generated using basic AI assistance. Please review and expand.)";

            $content = Content::create([
                'website_id' => $plan->website_id,
                'content_plan_id' => $plan->content_plan_id,
                'title' => $title,
                'content_type' => $plan->content_type ?? 'blog_post',
                'status' => 'draft',
                'content_data' => json_encode([
                    'body' => $body,
                    'keywords' => $finalKeywords,
                    'target_audience' => $targetAudience,
                    'ai_summary' => $summary, // Store the summary if generated
                ]),
                'source_url' => null,
                'guid' => null,
                'published_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Log::info("AI-assisted content generated with ID: " . $content->content_id);
            return $content;

        } catch (\Exception $e) {
            Log::error("Error generating AI-assisted content for plan ID: " . $plan->content_plan_id . " - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate content ideas based on keywords or topics.
     * Placeholder - could integrate with a title generation API.
     *
     * @param Website $website
     * @param array $keywords
     * @return array List of content ideas (strings).
     */
    public function generateContentIdeas(Website $website, array $keywords): array
    {
        Log::info("Generating content ideas (placeholder) for website ID: " . $website->website_id);
        // Placeholder: Keep simple generation for now, as free title APIs often require signup/complex setup
        // or are web tools rather than simple APIs.
        $ideas = [];
        foreach ($keywords as $keyword) {
            $ideas[] = "How to use {$keyword} effectively for your website";
            $ideas[] = "Top 5 benefits of {$keyword} in 2025";
            $ideas[] = "A beginner's guide to {$keyword}";
            $ideas[] = "{$keyword}: Common Mistakes to Avoid";
            $ideas[] = "The Future of {$keyword}";
        }
        // In a real implementation, this could call an external API (e.g., ApyHub, or others if found suitable).
        return array_slice($ideas, 0, 5); // Limit ideas
    }
}

