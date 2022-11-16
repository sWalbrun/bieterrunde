<?php

declare(strict_types=1);

use App\Models\BidderRound;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantTable extends Migration
{
    public function up(): void
    {
        Schema::create(Tenant::TABLE, function (Blueprint $table) {
            $table->string('id')->primary();

            $table->timestamps();
            $table->json('data')->nullable();
        });

        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->string(User::COL_FK_TENANT)->nullable();
            $table->dropUnique('user_email_unique');
        });
        Schema::table(
            BidderRound::TABLE,
            fn (Blueprint $table) => $table->string(BidderRound::COL_FK_TENANT)->nullable()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Tenant::TABLE);
        Schema::table(
            User::TABLE,
            function (Blueprint $table) {
                $table->dropColumn(User::COL_FK_TENANT);
            }
        );
        Schema::table(
            BidderRound::TABLE,
            fn (Blueprint $table) => $table->dropColumn(BidderRound::COL_FK_TENANT)
        );
    }
}
