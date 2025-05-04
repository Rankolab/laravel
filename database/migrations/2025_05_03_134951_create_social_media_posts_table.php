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
        Schema::create("social_media_posts", function (Blueprint $table) {
            $table->id("post_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->foreignId("content_id")->nullable()->constrained("content", "content_id")->onDelete("set null");
            $table->enum("platform", ["facebook", "instagram", "twitter", "threads", "linkedin", "quora", "medium", "reddit"]);
            $table->string("post_url")->nullable();
            $table->text("post_content");
            $table->dateTime("posted_at")->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("social_media_posts");
    }
};
