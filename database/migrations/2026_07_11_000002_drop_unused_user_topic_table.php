<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `user_topic` table is a leftover: participation in a topic is tracked
 * through the `share` table, and nothing in the codebase reads or writes
 * `user_topic`. Its restrictive foreign keys are only a latent deletion
 * hazard, so the table is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_topic');
    }

    public function down(): void
    {
        Schema::create('user_topic', function (Blueprint $table) {
            $table->id();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->foreignId('fkUser')->constrained('user');
            $table->foreignId('fkTopic')->constrained('topic');
        });
    }
};
