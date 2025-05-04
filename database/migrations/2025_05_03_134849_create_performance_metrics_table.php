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
        Schema::create("performance_metrics", function (Blueprint $table) {
            $table->id("performance_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->foreignId("content_id")->nullable()->constrained("content", "content_id")->onDelete("set null");
            $table->string("keyword", 100)->nullable();
            $table->integer("ranking")->nullable();
            $table->integer("clicks")->default(0);
            $table->integer("impressions")->default(0);
            $table->integer("affiliate_clicks")->default(0);
            $table->decimal("affiliate_earnings", 10, 2)->default(0.00);
            $table->enum("indexed_status", ["indexed", "not_indexed", "pending"])->default("pending");
            $table->dateTime("last_checked")->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("performance_metrics");
    }
};
