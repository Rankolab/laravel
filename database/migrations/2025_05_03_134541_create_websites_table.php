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
        Schema::create("websites", function (Blueprint $table) {
            $table->id("website_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("user_id")->constrained("users", "user_id")->onDelete("cascade");
            $table->string("domain")->unique();
            $table->string("niche", 100)->nullable();
            $table->enum("website_type", ["new", "existing"])->default("new");
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("websites");
    }
};
