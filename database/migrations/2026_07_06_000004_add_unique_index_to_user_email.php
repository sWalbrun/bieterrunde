<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The magic-link login looks users up by mail address across all tenants and
 * the app has always assumed one tenant per address — now the database
 * enforces it. Fails loudly when existing duplicates have to be resolved first.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table(User::TABLE)
            ->select(User::COL_EMAIL)
            ->groupBy(User::COL_EMAIL)
            ->havingRaw('COUNT(*) > 1')
            ->pluck(User::COL_EMAIL);
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot add unique index: duplicate user emails exist ('.$duplicates->implode(', ').'). Resolve them first.'
            );
        }

        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->unique(User::COL_EMAIL);
        });
    }

    public function down(): void
    {
        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->dropUnique([User::COL_EMAIL]);
        });
    }
};
