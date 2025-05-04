<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Website;
use App\Models\User;
use App\Models\RssFeed;
use App\Models\ContentPlan;
use App\Models\Content;
use App\Models\SocialMediaPost;
use App\Models\Newsletter;
use App\Services\SeoMetricsService;
use App\Services\RssFeedService;
use App\Services\ContentGenerationService;
use App\Services\PublishingService;
use App\Services\SocialMediaService;
use App\Services\NewsletterService;
use Carbon\Carbon;

class TestIntegrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:integrations {--domain=test.example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test external service integrations (SEO, RSS, Content Gen, Publish, Social, Newsletter)';

    /**
     * Execute the console command.
     */
    public function handle(
        SeoMetricsService $seoService,
        RssFeedService $rssService,
        ContentGenerationService $contentGenService,
        PublishingService $publishingService,
        SocialMediaService $socialMediaService,
        NewsletterService $newsletterService
    ) {
        $this->info('Starting integration tests...');
        Log::info('=== Starting Integration Test Run ===');

        // --- Setup Test Data ---
        $domain = $this->option('domain');
        // Corrected User creation block
        $user = User::firstOrCreate(['email' => 'testuser@example.com'], [
            'username' => 'testuser', // Added missing username
            'name' => 'Test User',
            'password_hash' => bcrypt('password'), // Corrected field name
            'license_key' => \Illuminate\Support\Str::uuid()
        ]);

        $website = Website::firstOrCreate(['domain' => $domain], [
            'user_id' => $user->user_id,
            'name' => 'Test Website',
            'status' => 'active'
        ]);

        $this->info("Using Website ID: {$website->website_id}, Domain: {$website->domain}");

        // --- Test SEO Metrics Service ---
        $this->line("\nTesting SeoMetricsService...");
        try {
            $this->info("Attempting PageSpeed update (requires GOOGLE_API_KEY in .env)...");
            $seoService->updatePageSpeedScore($website);
            $this->info("Attempting Search Console update (placeholder)...");
            $seoService->updateSearchConsoleMetrics($website);
            $this->info("Attempting DA/Backlinks update (placeholder)...");
            $seoService->updateDomainAuthorityAndBacklinks($website);
            $this->info("Attempting SEO Score update (placeholder)...");
            $seoService->updateSeoScore($website);
            $this->info("SeoMetricsService tests completed (check logs for details).");
        } catch (\Exception $e) {
            $this->error("SeoMetricsService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - SeoMetricsService Error: " . $e->getMessage());
        }

        // --- Test RSS Feed Service ---
        $this->line("\nTesting RssFeedService...");
        $rssFeed = RssFeed::firstOrCreate([
            'website_id' => $website->website_id,
            'feed_url' => 'https://example.com/nonexistent-feed.xml' // Use a dummy URL
        ], [
            'feed_name' => 'Test Feed', // Added missing feed_name
            'status' => 'active',
            'last_checked' => null
        ]);
        try {
            $this->info("Attempting to process dummy RSS feed: {$rssFeed->feed_url}");
            $rssService->processFeed($rssFeed);
            $this->info("RssFeedService test completed (check logs for expected errors/parsing results).");
        } catch (\Exception $e) {
            $this->error("RssFeedService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - RssFeedService Error: " . $e->getMessage());
        }

        // --- Test Content Generation Service ---
        $this->line("\nTesting ContentGenerationService...");
        $contentPlan = ContentPlan::firstOrCreate([
            'website_id' => $website->website_id,
            'topic' => 'Test Topic'
        ], [
            'keywords' => ['test', 'generation'],
            'content_type' => 'blog_post',
            'status' => 'approved'
        ]);
        try {
            $this->info("Attempting placeholder content generation...");
            $generatedContent = $contentGenService->generateContent($contentPlan);
            if ($generatedContent) {
                $this->info("Placeholder content generated with ID: {$generatedContent->content_id}");
            } else {
                $this->warn("Placeholder content generation failed.");
            }
            $this->info("Attempting placeholder content idea generation...");
            $ideas = $contentGenService->generateContentIdeas($website, ['keyword1', 'keyword2']);
            $this->info("Generated ideas (placeholder): " . implode(', ', $ideas));
            $this->info("ContentGenerationService tests completed.");
        } catch (\Exception $e) {
            $this->error("ContentGenerationService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - ContentGenerationService Error: " . $e->getMessage());
        }

        // --- Test Publishing Service ---
        $this->line("\nTesting PublishingService...");
        $contentToPublish = Content::firstOrCreate([
            'website_id' => $website->website_id,
            'title' => 'Test Content for Publishing',
        ], [
            'body' => 'This is the test body content.', // Added missing body
            'word_count' => 5, // Added missing word_count
            'status' => 'draft',
            'content_type' => 'page', // Added based on original API docs, assuming it's needed
            'content_data' => json_encode(['body' => 'Test body in data field'])
        ]);
        try {
            $this->info("Attempting placeholder publish to WordPress...");
            $publishingService->publishContent($contentToPublish, 'wordpress');
            $this->info("Attempting placeholder publish to Google Indexing...");
            $publishingService->publishContent($contentToPublish, 'google_indexing');
            $this->info("PublishingService tests completed (check logs).");
        } catch (\Exception $e) {
            $this->error("PublishingService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - PublishingService Error: " . $e->getMessage());
        }

        // --- Test Social Media Service ---
        $this->line("\nTesting SocialMediaService...");
        $socialPost = SocialMediaPost::firstOrCreate([
            'website_id' => $website->website_id,
            'post_content' => 'Test social media post from Rankolab! #testing' // Corrected key
        ], [
            'platform' => 'twitter',
            'status' => 'pending',
            'scheduled_at' => null
        ]);
        try {
            $this->info("Attempting post to Twitter (requires .env config)...");
            $socialMediaService->postToPlatform($socialPost, 'twitter');
            $this->info("Attempting placeholder post to Facebook...");
            $socialMediaService->postToPlatform($socialPost, 'facebook');
            $this->info("Attempting placeholder schedule...");
            $socialMediaService->schedulePost($socialPost, Carbon::now()->addDay());
            $this->info("SocialMediaService tests completed (check logs).");
        } catch (\Exception $e) {
            $this->error("SocialMediaService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - SocialMediaService Error: " . $e->getMessage());
        }

        // --- Test Newsletter Service ---
        $this->line("\nTesting NewsletterService...");
        $newsletter = Newsletter::firstOrCreate([
            'website_id' => $website->website_id,
            'subject' => 'Test Newsletter Subject'
        ], [
            'user_id' => $user->user_id, // Added missing user_id
            'content' => '<p>This is a test newsletter.</p>',
            'status' => 'draft'
        ]);
        try {
            $this->info("Attempting to send newsletter (requires mail config in .env)...");
            // Using a dummy subscriber list for testing
            $testSubscribers = ['test-subscriber@example.com']; 
            $newsletterService->sendNewsletter($newsletter, $testSubscribers);
            $this->info("NewsletterService test completed (check logs).");
        } catch (\Exception $e) {
            $this->error("NewsletterService Error: " . $e->getMessage());
            Log::error("TestIntegrationsCommand - NewsletterService Error: " . $e->getMessage());
        }

        Log::info('=== Integration Test Run Finished ===');
        $this->info("\nIntegration tests finished. Please check Laravel logs for detailed results.");

        return Command::SUCCESS;
    }
}

