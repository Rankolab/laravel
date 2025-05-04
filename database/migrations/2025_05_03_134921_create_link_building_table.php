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
        Schema::create("link_building", function (Blueprint $table) {
            $table->id("link_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->enum("link_type", ["blog_comment", "directory", "web2", "guest_post"]);
            $table->string("target_url");
            $table->string("anchor_text", 100)->nullable();
            $table->integer("domain_authority")->default(0);
            $table->integer("spam_score")->default(0);
            $table->enum("status", ["active", "pending", "rejected"])->default("pending");
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("link_building");
    }
};
