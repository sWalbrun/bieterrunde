<?php

use App\Models\Offer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks whether an offer was submitted by the member themselves or entered
 * by an admin on their behalf (see github issue #13).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Offer::TABLE, function (Blueprint $table) {
            $table->boolean(Offer::COL_ENTERED_BY_ADMIN)->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Offer::TABLE, function (Blueprint $table) {
            $table->dropColumn(Offer::COL_ENTERED_BY_ADMIN);
        });
    }
};
