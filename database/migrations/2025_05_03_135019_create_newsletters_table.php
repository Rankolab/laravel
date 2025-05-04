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
        Schema::create("newsletters", function (Blueprint $table) {
            $table->id("newsletter_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("user_id")->constrained("users", "user_id")->onDelete("cascade");
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->string("subject");
            $table->text("content");
            $table->dateTime("sent_at")->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("newsletters");
    }
};
