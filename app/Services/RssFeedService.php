<?php

namespace App\Services;

use App\Models\RssFeed;
use App\Models\Content;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SimplePie\SimplePie;

class RssFeedService
{
    /**
     * Fetch and process items from a single RSS feed.
     * Uses the free and open-source SimplePie library.
     *
     * @param RssFeed $feed The RSS feed model instance.
     * @return bool True on success, false on failure.
     */
    public function processFeed(RssFeed $feed): bool
    {
        Log::info("Processing RSS feed: " . $feed->feed_url . " for website ID: " . $feed->website_id);

        try {
            $simplepie = new SimplePie();
            $simplepie->set_feed_url($feed->feed_url);
            $simplepie->enable_cache(false); // Disable cache for frequent updates, or configure properly
            // Consider adding user agent: $simplepie->set_useragent('Rankolab Bot/1.0');
            $simplepie->init();
            $simplepie->handle_content_type();

            if ($simplepie->error()) {
                Log::error("Error fetching/parsing RSS feed " . $feed->feed_url . ": " . $simplepie->error());
                $feed->last_checked = Carbon::now();
                $feed->status = 'error';
                $feed->save();
                return false;
            }

            $itemsProcessed = 0;
            foreach ($simplepie->get_items() as $item) {
                // Check if this item has already been processed based on GUID or link
                $guid = $item->get_id(); // SimplePie uses get_id() for guid
                $link = $item->get_permalink();
                $uniqueIdentifier = $guid ?: $link;

                if (empty($uniqueIdentifier)) {
                    Log::warning("Skipping RSS item with no GUID or link from feed: " . $feed->feed_url);
                    continue;
                }

                $existingContent = Content::where('source_url', $link)
                                        ->orWhere('guid', $guid)
                                        ->where('website_id', $feed->website_id)
                                        ->first();

                if (!$existingContent) {
                    // Create a new Content record (initially as 'draft' or 'pending')
                    Content::create([
                        'website_id' => $feed->website_id,
                        'title' => $item->get_title() ?? 'Untitled',
                        'content_type' => 'rss_import',
                        'status' => 'pending_review', // Or 'draft'
                        'content_data' => json_encode([
                            'description' => $item->get_description(),
                            'content_encoded' => $item->get_content(), // Full content if available
                            'author' => $item->get_author() ? $item->get_author()->get_name() : null,
                            'categories' => $item->get_categories() ? array_map(fn($cat) => $cat->get_label(), $item->get_categories()) : [],
                        ]),
                        'source_url' => $link,
                        'guid' => $guid,
                        'published_at' => $item->get_date('Y-m-d H:i:s') ? Carbon::parse($item->get_date('Y-m-d H:i:s')) : Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                    $itemsProcessed++;
                }
            }

            $feed->last_checked = Carbon::now();
            $feed->status = 'active';
            $feed->save();

            Log::info("Finished processing RSS feed: " . $feed->feed_url . ". New items added: " . $itemsProcessed);
            return true;

        } catch (\Exception $e) {
            Log::error("Exception processing RSS feed " . $feed->feed_url . ": " . $e->getMessage());
            $feed->last_checked = Carbon::now();
            $feed->status = 'error';
            $feed->save();
            return false;
        }
    }

    /**
     * Process all active RSS feeds for a given website.
     *
     * @param Website $website
     */
    public function processAllFeedsForWebsite(Website $website)
    {
        Log::info("Starting RSS feed processing for website ID: " . $website->website_id);
        $feeds = $website->rssFeeds()->where('status', 'active')->get();
        $successCount = 0;
        $failCount = 0;

        foreach ($feeds as $feed) {
            if ($this->processFeed($feed)) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        Log::info("Finished RSS feed processing for website ID: " . $website->website_id . ". Success: " . $successCount . ", Failed: " . $failCount);
    }
}

