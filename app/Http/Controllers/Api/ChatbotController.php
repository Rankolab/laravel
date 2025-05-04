<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\ChatbotLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    /**
     * Log a chatbot interaction.
     * Note: This endpoint assumes it receives the interaction details to log.
     * Actual chatbot response generation would likely happen elsewhere or via an external service.
     */
    public function logInteraction(Request $request, $website_id)
    {
        // Check if the website exists (no user auth check needed as per API doc for this endpoint?)
        // However, it's safer to assume it should be tied to an authenticated user's website.
        // Re-adding user check for security.
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "session_id" => "required|string|max:255",
            "user_message" => "required|string",
            "bot_response" => "required|string",
            // user_id can be inferred from authenticated user if needed, or passed explicitly
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => "Invalid chatbot log parameters", "details" => $validator->errors()], 400);
        }

        $chatbotLog = ChatbotLog::create([
            "website_id" => $website_id,
            "user_id" => $user->user_id, // Log the authenticated user
            "session_id" => $request->input("session_id"),
            "user_message" => $request->input("user_message"),
            "bot_response" => $request->input("bot_response"),
            "timestamp" => Carbon::now(),
        ]);

        // Return response as per API documentation
        $response = [
            "log_id" => $chatbotLog->log_id,
            "session_id" => $chatbotLog->session_id,
            "timestamp" => $chatbotLog->timestamp->toIso8601String(),
        ];

        return response()->json($response, 201);
    }

    // Add other methods if needed, e.g., retrieving chat history
}
