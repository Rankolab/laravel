<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\WebsiteDesign;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class WebsiteDesignController extends Controller
{
    /**
     * Store or update website design configuration.
     */
    public function storeOrUpdate(Request $request, $website_id)
    {
        $user = Auth::user();
        $website = Website::where("website_id", $website_id)->where("user_id", $user->user_id)->first();

        if (!$website) {
            return response()->json(["error" => "Website not found or access denied"], 404);
        }

        $validator = Validator::make($request->all(), [
            "template_name" => "required|string|max:100",
            "color_scheme" => "nullable|array", // Validate as array (Laravel handles JSON decode)
            "color_scheme.primary" => "nullable|string|max:7", // Example validation for nested JSON
            "color_scheme.secondary" => "nullable|string|max:7",
            "typography" => "nullable|array",
            "typography.font_family" => "nullable|string|max:100",
            "typography.font_size" => "nullable|string|max:10",
            "layout" => "nullable|string|max:50",
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => "Invalid design parameters", "details" => $validator->errors()], 400);
        }

        // Use updateOrCreate to handle both creation and update scenarios
        $design = WebsiteDesign::updateOrCreate(
            ["website_id" => $website_id], // Find by website_id
            [
                "template_name" => $request->input("template_name"),
                "color_scheme" => $request->input("color_scheme"),
                "typography" => $request->input("typography"),
                "layout" => $request->input("layout", "default"), // Use default if not provided
            ]
        );

        // Return response as per API documentation
        $response = [
            "design_id" => $design->design_id,
            "website_id" => $design->website_id,
            "template_name" => $design->template_name,
        ];

        // Use 201 Created if it was newly created, 200 OK if updated
        $statusCode = $design->wasRecentlyCreated ? 201 : 200;

        return response()->json($response, $statusCode);
    }
}
