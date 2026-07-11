<?php

use App\Models\BidderRoundActionLog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers admin-triggered bidder-round actions (announcing the start,
 * reminding participants) with the acting admin and timestamp, so several
 * admins can coordinate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create(BidderRoundActionLog::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(BidderRoundActionLog::COL_FK_BIDDER_ROUND)
                ->constrained('bidderRound')
                ->cascadeOnDelete();
            // Nullable: system-triggered actions (e.g. scheduled reminders) have no admin
            $table->foreignId(BidderRoundActionLog::COL_FK_USER)
                ->nullable()
                ->constrained('user')
                ->nullOnDelete();
            $table->string(BidderRoundActionLog::COL_ACTION);
            $table->unsignedInteger(BidderRoundActionLog::COL_RECIPIENT_COUNT)->default(0);
            $table->timestamp(BidderRoundActionLog::COL_CREATED_AT)->nullable();
            $table->timestamp(BidderRoundActionLog::COL_UPDATED_AT)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(BidderRoundActionLog::TABLE);
    }
};
