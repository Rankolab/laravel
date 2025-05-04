<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("social_media_posts", function (Blueprint $table) {
            // Add the status column, defaulting to 'pending'
            $table->enum("status", ["pending", "scheduled", "posted", "failed"])->default("pending")->after("platform");
            // Add scheduled_at column, as it seems to be used in the service
            $table->dateTime("scheduled_at")->nullable()->after("post_content");
            // Add external_id column, as it seems to be used in the service
            $table->string("external_id")->nullable()->after("post_url");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("social_media_posts", function (Blueprint $table) {
            $table->dropColumn("status");
            $table->dropColumn("scheduled_at");
            $table->dropColumn("external_id");
        });
    }
};

