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
        Schema::table("newsletters", function (Blueprint $table) {
            // Add the status column, defaulting to 'draft'
            $table->enum("status", ["draft", "sending", "sent", "failed", "partial_failure"])->default("draft")->after("content");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("newsletters", function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
};

