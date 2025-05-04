<?php

namespace App\Services;

use App\Models\SocialMediaPost;
use App\Models\Website;
use Illuminate\Support\Facades\Log;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;

class SocialMediaService
{
    /**
     * Post content to a social media platform.
     *
     * NOTE: This implementation focuses on Twitter using a free library.
     * Other platforms (Facebook, LinkedIn, Instagram) require their own APIs, SDKs, and developer app setups.
     * Credentials (API keys, secrets, tokens) must be securely stored (e.g., in .env or a dedicated config).
     *
     * @param SocialMediaPost $post The post details.
     * @param string $platform The target platform (e.g., 'twitter', 'facebook').
     * @return bool True on success, false on failure.
     */
    public function postToPlatform(SocialMediaPost $post, string $platform): bool
    {
        Log::info("Attempting to post social media content ID: " . $post->social_media_post_id . " to platform: " . $platform);

        try {
            switch (strtolower($platform)) {
                case 'twitter':
                    return $this->postToTwitter($post);

                case 'facebook':
                    Log::info("Placeholder: Posting to Facebook for post ID " . $post->social_media_post_id . ". Requires Facebook SDK/API setup.");
                    // Simulate success for now, update status if needed
                    $post->status = 'posted'; // Or 'scheduled' if applicable
                    $post->posted_at = Carbon::now();
                    $post->save();
                    return true;

                case 'linkedin':
                    Log::info("Placeholder: Posting to LinkedIn for post ID " . $post->social_media_post_id . ". Requires LinkedIn API setup.");
                    // Simulate success
                    $post->status = 'posted';
                    $post->posted_at = Carbon::now();
                    $post->save();
                    return true;

                // Add other platforms as needed

                default:
                    Log::warning("Unsupported social media platform: " . $platform . " for post ID: " . $post->social_media_post_id);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Error posting social media content ID: " . $post->social_media_post_id . " to platform: " . $platform . " - " . $e->getMessage());
            $post->status = 'failed';
            $post->save();
            return false;
        }
    }

    /**
     * Post content specifically to Twitter.
     * Uses the free abraham/twitteroauth library.
     * Requires Twitter API v2 credentials (API Key, Secret, Access Token, Secret) stored securely.
     *
     * @param SocialMediaPost $post
     * @return bool
     */
    private function postToTwitter(SocialMediaPost $post): bool
    {
        $apiKey = env('TWITTER_API_KEY');
        $apiSecretKey = env('TWITTER_API_SECRET_KEY');
        $accessToken = env('TWITTER_ACCESS_TOKEN');
        $accessTokenSecret = env('TWITTER_ACCESS_TOKEN_SECRET');

        if (!$apiKey || !$apiSecretKey || !$accessToken || !$accessTokenSecret) {
            Log::error("Twitter API credentials are not fully configured in .env. Cannot post tweet for post ID: " . $post->social_media_post_id);
            $post->status = 'failed';
            $post->save();
            return false;
        }

        try {
            $connection = new TwitterOAuth($apiKey, $apiSecretKey, $accessToken, $accessTokenSecret);
            $connection->setApiVersion('2'); // Use API v2

            // Simple text post
            $payload = ['text' => $post->content];

            // TODO: Add image/video upload support if needed, which is more complex

            $response = $connection->post("tweets", $payload); // Pass payload directly, library handles JSON for v2

            if ($connection->getLastHttpCode() == 201) {
                Log::info("Successfully posted tweet for post ID: " . $post->social_media_post_id . ". Tweet ID: " . ($response->data->id ?? 'N/A'));
                $post->status = 'posted';
                $post->posted_at = Carbon::now();
                $post->external_id = $response->data->id ?? null; // Store the tweet ID
                $post->save();
                return true;
            } else {
                Log::error("Error posting tweet for post ID: " . $post->social_media_post_id . ". HTTP Code: " . $connection->getLastHttpCode() . " - Response: " . json_encode($response));
                $post->status = 'failed';
                $post->save();
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception posting tweet for post ID: " . $post->social_media_post_id . " - " . $e->getMessage());
            $post->status = 'failed';
            $post->save();
            return false;
        }
    }

    /**
     * Schedule a post (placeholder).
     *
     * @param SocialMediaPost $post
     * @param Carbon $scheduleTime
     * @return bool
     */
    public function schedulePost(SocialMediaPost $post, Carbon $scheduleTime): bool
    {
        Log::info("Scheduling social media post ID: " . $post->social_media_post_id . " for " . $scheduleTime->toIso8601String());
        // In a real app, this would update the post status and rely on a scheduled job runner (like Laravel Scheduler)
        // to pick up posts whose schedule_time has passed and call postToPlatform.
        $post->status = 'scheduled';
        $post->scheduled_at = $scheduleTime;
        $post->posted_at = null; // Ensure posted_at is null for scheduled posts
        $post->save();
        return true; // Return true assuming scheduling itself worked
    }
}

