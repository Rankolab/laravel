<?php

namespace App\Services;

use App\Models\Newsletter;
use App\Models\Website;
use App\Models\User; // Assuming users subscribe to newsletters
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Carbon\Carbon;

class NewsletterService
{
    protected $mailer;

    public function __construct()
    {
        // Configure mailer based on .env settings
        // Example DSN: smtp://user:pass@smtp.example.com:port
        // Or use other transports: https://symfony.com/doc/current/mailer.html#transport-setup
        $dsn = sprintf(
            '%s://%s:%s@%s:%s', // Use smtp for standard SMTP
            env('MAIL_MAILER', 'smtp'),
            env('MAIL_USERNAME'),
            env('MAIL_PASSWORD'),
            env('MAIL_HOST', 'smtp.mailgun.org'),
            env('MAIL_PORT', 587)
            // Add encryption if needed: ?encryption=tls
        );

        // Validate essential mail config
        if (!env('MAIL_USERNAME') || !env('MAIL_PASSWORD') || !env('MAIL_HOST') || !env('MAIL_FROM_ADDRESS')) {
            Log::warning('NewsletterService: Mailer is not fully configured in .env. Newsletter sending will likely fail.');
            $this->mailer = null; // Indicate mailer is not configured
        } else {
            try {
                $transport = Transport::fromDsn($dsn);
                $this->mailer = new Mailer($transport);
            } catch (\Exception $e) {
                Log::error('NewsletterService: Failed to initialize mailer transport: ' . $e->getMessage());
                $this->mailer = null;
            }
        }
    }

    /**
     * Send a newsletter to subscribers.
     * Uses the free and open-source Symfony Mailer component.
     * Requires SMTP credentials configured in .env.
     *
     * @param Newsletter $newsletter The newsletter content to send.
     * @param array $subscriberEmails List of email addresses to send to.
     * @return bool True if sending was attempted (check logs for individual errors), false if mailer not configured.
     */
    public function sendNewsletter(Newsletter $newsletter, array $subscriberEmails): bool
    {
        if (!$this->mailer) {
            Log::error('Cannot send newsletter ID: ' . $newsletter->newsletter_id . '. Mailer is not configured.');
            $newsletter->status = 'failed';
            $newsletter->save();
            return false;
        }

        if (empty($subscriberEmails)) {
            Log::info('No subscribers to send newsletter ID: ' . $newsletter->newsletter_id);
            $newsletter->status = 'sent'; // Or 'skipped'
            $newsletter->sent_at = Carbon::now();
            $newsletter->save();
            return true;
        }

        Log::info('Sending newsletter ID: ' . $newsletter->newsletter_id . ' to ' . count($subscriberEmails) . ' subscribers.');

        $email = (new Email())
            ->from(env('MAIL_FROM_ADDRESS', 'noreply@example.com'))
            ->subject($newsletter->subject)
            ->html($newsletter->content); // Assuming content is HTML
            // ->text(strip_tags($newsletter->content)); // Optional: Add plain text version

        $failures = 0;
        foreach ($subscriberEmails as $subscriberEmail) {
            // Basic email validation
            if (!filter_var($subscriberEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Skipping invalid subscriber email: ' . $subscriberEmail . ' for newsletter ID: ' . $newsletter->newsletter_id);
                $failures++;
                continue;
            }

            try {
                // Create a new email object for each recipient to avoid issues with some transports
                $individualEmail = clone $email;
                $individualEmail->to($subscriberEmail);

                $this->mailer->send($individualEmail);
                // Consider adding slight delay between sends to avoid rate limits
                // usleep(100000); // 100ms delay

            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                Log::error('Failed to send newsletter ID: ' . $newsletter->newsletter_id . ' to ' . $subscriberEmail . ': ' . $e->getMessage());
                $failures++;
            } catch (\Exception $e) {
                Log::error('Generic error sending newsletter ID: ' . $newsletter->newsletter_id . ' to ' . $subscriberEmail . ': ' . $e->getMessage());
                $failures++;
            }
        }

        $newsletter->status = ($failures === 0) ? 'sent' : 'partial_failure';
        $newsletter->sent_at = Carbon::now();
        // Optionally store send statistics (e.g., success/failure count)
        // $newsletter->send_details = json_encode(['total' => count($subscriberEmails), 'failures' => $failures]);
        $newsletter->save();

        Log::info('Finished sending newsletter ID: ' . $newsletter->newsletter_id . '. Failures: ' . $failures);
        return true; // Return true as the process completed, check logs/status for details
    }

    /**
     * Get subscribers for a specific website (placeholder logic).
     *
     * @param Website $website
     * @return array List of subscriber email addresses.
     */
    public function getSubscribers(Website $website): array
    {
        // In a real application, this would query a subscribers table or list associated with the website.
        // For now, as a basic implementation, we assume the website owner is the primary subscriber.
        Log::info("Fetching subscribers (basic implementation: website owner) for website ID: " . $website->website_id);

        $owner = $website->user; // Get the associated user

        if ($owner && $owner->email) {
            return [$owner->email]; // Return the owner's email in an array
        } 

        Log::warning("Could not find owner or owner email for website ID: " . $website->website_id . ". Returning empty subscriber list.");
        return []; // Return empty list if owner or email not found
    }

    /**
     * Send a specific newsletter associated with a website.
     *
     * @param Newsletter $newsletter
     * @return bool
     */
    public function sendWebsiteNewsletter(Newsletter $newsletter): bool
    {
        if (!$newsletter->website) {
            Log::error('Cannot send newsletter ID: ' . $newsletter->newsletter_id . '. No associated website found.');
            return false;
        }

        $subscribers = $this->getSubscribers($newsletter->website);
        return $this->sendNewsletter($newsletter, $subscribers);
    }
}

