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
        Schema::create("affiliate_program", function (Blueprint $table) {
            $table->id("affiliate_id"); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->foreignId("user_id")->constrained("users", "user_id")->onDelete("cascade");
            $table->string("affiliate_code", 50)->unique();
            $table->integer("total_clicks")->default(0);
            $table->decimal("total_earnings", 10, 2)->default(0.00);
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("affiliate_program");
    }
};
