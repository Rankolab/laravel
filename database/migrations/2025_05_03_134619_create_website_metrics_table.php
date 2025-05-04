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
        Schema::create("website_metrics", function (Blueprint $table) {
            $table->id("metric_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->integer("domain_authority")->default(0);
            $table->integer("seo_score")->default(0);
            $table->integer("backlinks_count")->default(0);
            $table->integer("page_speed_score")->default(0);
            $table->dateTime("last_analyzed")->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("website_metrics");
    }
};
