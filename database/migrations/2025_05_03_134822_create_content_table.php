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
        Schema::create("content", function (Blueprint $table) {
            $table->id("content_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->foreignId("plan_id")->nullable()->constrained("content_plans", "plan_id")->onDelete("set null");
            $table->string("title");
            $table->text("body");
            $table->integer("word_count");
            $table->string("featured_image_url")->nullable();
            $table->json("images")->nullable();
            $table->json("internal_links")->nullable();
            $table->json("external_links")->nullable();
            $table->json("affiliate_links")->nullable();
            $table->json("keywords")->nullable();
            $table->enum("status", ["draft", "published", "scheduled"])->default("draft");
            $table->dateTime("published_at")->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("content");
    }
};
