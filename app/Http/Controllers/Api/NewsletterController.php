<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Newsletter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NewsletterController extends Controller
{
    /**
     * Store (schedule) a new newsletter for a website.
     * Note: Actual sending of newsletters will be handled later.
     */
    public function store(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "subject" => "required|string|max:255",
            "body" => "required|string",
            "status" => "required|string|in:draft,scheduled,sent,failed",
            "scheduled_at" => "nullable|date_format:Y-m-d H:i:s", // Validate datetime format
        ]);

        if ($validator->fails()) {
            // Check for specific subject error as per API doc example
            if ($validator->errors()->has("subject")) {
                 return response()->json(["error" => "Invalid subject"], 400);
            }
            return response()->json(["error" => "Invalid newsletter parameters", "details" => $validator->errors()], 400);
        }

        // Ensure scheduled_at is provided if status is scheduled
        if ($request->input("status") === "scheduled" && !$request->input("scheduled_at")) {
            return response()->json(["error" => "Scheduled time is required for scheduled newsletters"], 400);
        }

        $newsletter = Newsletter::create([
            "website_id" => $website_id,
            "subject" => $request->input("subject"),
            "body" => $request->input("body"),
            "status" => $request->input("status"),
            "scheduled_at" => $request->input("scheduled_at") ? Carbon::parse($request->input("scheduled_at")) : null,
            "sent_at" => null,
        ]);

        // Placeholder: If status is 'scheduled', trigger a job for sending later.
        // If status is 'draft', just save it.
        // If status is 'sent' (immediate send), trigger sending job now (not implemented here).

        // Return response as per API documentation
        $response = [
            "newsletter_id" => $newsletter->newsletter_id,
            "website_id" => $newsletter->website_id,
            "subject" => $newsletter->subject,
            "status" => $newsletter->status,
        ];

        return response()->json($response, 201);
    }

    // Add other methods for managing newsletters if needed (e.g., update, delete, list)
}
