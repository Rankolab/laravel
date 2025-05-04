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
        Schema::create("rss_feeds", function (Blueprint $table) {
            $table->id("feed_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->string("feed_url");
            $table->string("feed_name", 100);
            $table->integer("quantity")->default(1);
            $table->integer("word_count")->default(1000);
            $table->json("schedule")->nullable();
            // $table->boolean("is_active")->default(true); // Replaced with status
            $table->string("status", 50)->default("active"); // Added status field
            $table->timestamp("last_checked")->nullable(); // Added last_checked field
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rss_feeds");
    }
};

