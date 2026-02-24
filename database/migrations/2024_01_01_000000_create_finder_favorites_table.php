<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Favorites / Places" table stores user-pinned paths,
     * similar to elFinder's "Places" sidebar section.
     */
    public function up(): void
    {
        Schema::create('finder_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 50);
            $table->string('path', 1024);
            $table->string('name', 255)->nullable(); // Custom label
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'disk', 'path']);
            $table->index(['user_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finder_favorites');
    }
};
