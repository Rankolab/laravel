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
        Schema::create("website_designs", function (Blueprint $table) {
            $table->id("design_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("website_id")->constrained("websites", "website_id")->onDelete("cascade");
            $table->string("template_name", 100);
            $table->json("color_scheme")->nullable();
            $table->json("typography")->nullable();
            $table->string("layout", 50)->default("default");
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("website_designs");
    }
};
