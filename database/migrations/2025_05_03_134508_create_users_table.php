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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // Corresponds to BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('email')->unique();
            $table->string('username', 50)->unique();
            $table->string('password_hash');
            $table->string('license_key', 100)->unique()->nullable();
            $table->enum('license_status', ['active', 'inactive', 'expired', 'trial'])->default('inactive');
            $table->enum('license_type', ['trial', 'monthly', 'yearly'])->nullable();
            $table->dateTime('license_start_date')->nullable();
            $table->dateTime('license_end_date')->nullable();
            $table->timestamps(); // Corresponds to created_at and updated_at TIMESTAMPs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
