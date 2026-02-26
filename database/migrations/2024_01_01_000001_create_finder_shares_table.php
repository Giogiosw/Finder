<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Shares" table stores public share links for files/directories.
     */
    public function up(): void
    {
        Schema::create('finder_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 50);
            $table->string('path', 1024);
            $table->string('token', 64)->unique();
            $table->boolean('is_directory')->default(false);
            $table->string('password', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_downloads')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('allow_upload')->default(false);
            $table->timestamps();

            $table->index(['token']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finder_shares');
    }
};
