# Rankolab Laravel Backend - External Service Integrations

This document outlines the approach taken for integrating external services into the Rankolab Laravel backend, focusing exclusively on free and open-source APIs and libraries as requested.

## 1. Overall Approach

External functionalities were encapsulated within dedicated service classes in the `app/Services` directory. Each service handles interactions with specific external APIs or performs related tasks. Dependency injection is used via Laravel's service container to provide these services where needed (e.g., in controllers or console commands).

Configuration details, such as API keys and credentials, are expected to be stored securely in the `.env` file.

## 2. Service-Specific Integrations

### 2.1. SEO Metrics Service (`SeoMetricsService`)

*   **Functionality**: Fetches SEO-related metrics for websites.
*   **APIs/Libraries Used**:
    *   **PageSpeed Insights**: Uses the official `google/apiclient` library to interact with the Google PageSpeed Insights API (v5). This API is free but requires a `GOOGLE_API_KEY` configured in `.env`.
    *   **Search Console Metrics**: Placeholder implementation. Integration with the Google Search Console API (free) is possible using `google/apiclient` but requires user-specific OAuth 2.0 setup (service account or user consent flow), which is beyond the scope of basic backend setup and needs user interaction.
    *   **Domain Authority & Backlinks**: Placeholder implementation. Reliable DA and backlink data typically require paid APIs (e.g., Moz, Ahrefs, SEMrush). Free options are very limited and often inaccurate.
    *   **SEO Score**: Placeholder implementation. This is a composite score derived from other metrics; the calculation logic needs to be defined based on available data.
*   **Limitations**: Only PageSpeed score is actively fetched. Search Console requires user OAuth setup. DA/Backlinks require paid APIs for reliable data.

### 2.2. RSS Feed Service (`RssFeedService`)

*   **Functionality**: Fetches and processes RSS/Atom feeds to import content items.
*   **APIs/Libraries Used**: Uses the `simplepie/simplepie` library, a robust and widely-used open-source feed parsing library for PHP.
*   **Limitations**: Relies on the quality and availability of the source RSS feeds. Does not perform content transformation or advanced filtering beyond checking for existing GUIDs/links.

### 2.3. Content Generation Service (`ContentGenerationService`)

*   **Functionality**: Generates content drafts based on content plans, incorporating basic AI assistance for summarization and keyword extraction.
*   **APIs/Libraries Used**:
    *   **ApyHub Summarize API**: Uses the `Illuminate\Support\Facades\Http` client to interact with the ApyHub Text Summarization API (`https://api.apyhub.com/ai/summarize-text`). This API offers a free tier and requires an `APYHUB_API_KEY` configured in `.env` (using `apy-token` header).
    *   **Cortical.io Keywords API**: Uses the `Illuminate\Support\Facades\Http` client to interact with the Cortical.io Keywords Extraction API (`https://api.cortical.io/rest/text/keywords`). This API offers a free tier and requires a `CORTICAL_API_KEY` configured in `.env` (using `Authorization: Bearer` header).
*   **Limitations**: The AI integration provides basic assistance (short summaries, keyword suggestions) based on free tiers. It does not perform full content writing. The quality depends on the external APIs. Title/idea generation remains a placeholder. Requires API keys for ApyHub and Cortical.io to be obtained and configured.

### 2.4. Publishing Service (`PublishingService`)

*   **Functionality**: Publishes content to external platforms.
*   **APIs/Libraries Used**:
    *   **WordPress**: Placeholder implementation. Publishing to WordPress typically requires using its REST API or XML-RPC, needing site-specific credentials (URL, user, application password/token).
    *   **Google Indexing API**: Placeholder implementation. Uses the free Google Indexing API via `google/apiclient` but requires website-specific OAuth 2.0 service account credentials configured.
*   **Limitations**: All publishing functions are placeholders. Real implementation requires platform-specific credentials and API interactions.

### 2.5. Social Media Service (`SocialMediaService`)

*   **Functionality**: Posts content to social media platforms.
*   **APIs/Libraries Used**:
    *   **Twitter**: Uses the `abraham/twitteroauth` library (open-source) to interact with the Twitter API v2. Requires Twitter developer app credentials (`TWITTER_API_KEY`, `TWITTER_API_SECRET_KEY`, `TWITTER_ACCESS_TOKEN`, `TWITTER_ACCESS_TOKEN_SECRET`) configured in `.env`.
    *   **Facebook/LinkedIn/Others**: Placeholder implementations. Each platform requires its own SDK, developer app setup, and API credentials.
*   **Limitations**: Only basic text posting to Twitter is implemented. Other platforms and advanced features (image/video uploads) are placeholders. Twitter API usage is subject to rate limits and platform policies.

### 2.6. Newsletter Service (`NewsletterService`)

*   **Functionality**: Sends newsletters to subscribers.
*   **APIs/Libraries Used**: Uses the `symfony/mailer` component (open-source). Relies on standard SMTP or other Symfony Mailer transports configured via `.env` variables (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`).
*   **Limitations**: Sending reliability and deliverability depend heavily on the chosen mail transport/provider (e.g., SendGrid, Mailgun, Postmark, or a standard SMTP server) and its configuration/reputation. Subscriber list management (`getSubscribers` method) is currently a placeholder.

## 3. Configuration Summary

The following `.env` variables are crucial for the implemented integrations:

*   `GOOGLE_API_KEY`: For Google PageSpeed Insights.
*   `TWITTER_API_KEY`, `TWITTER_API_SECRET_KEY`, `TWITTER_ACCESS_TOKEN`, `TWITTER_ACCESS_TOKEN_SECRET`: For Twitter posting.
*   `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: For sending emails/newsletters via Symfony Mailer.
*   `APYHUB_API_KEY`: For ApyHub Text Summarization API (free tier).
*   `CORTICAL_API_KEY`: For Cortical.io Keyword Extraction API (free tier).

Placeholders for Google OAuth (Search Console, Indexing API) and WordPress credentials would require additional configuration if implemented.

## 4. Conclusion

The backend integrations prioritize free and open-source solutions. Core functionalities like RSS processing, basic Twitter posting, PageSpeed checks, and email sending are implemented using standard libraries. Significant limitations exist where industry-standard solutions rely on paid APIs (advanced SEO, AI content generation) or require complex, user-specific authorization setups (Search Console, publishing platforms). These areas are implemented as placeholders, clearly indicating the need for further development or configuration if full functionality is required.

