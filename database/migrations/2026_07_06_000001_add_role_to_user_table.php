<?php

use App\Enums\EnumRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->string(User::COL_ROLE, 20)->default(EnumRole::MEMBER->value);
        });
    }

    public function down(): void
    {
        Schema::table(User::TABLE, function (Blueprint $table) {
            $table->dropColumn(User::COL_ROLE);
        });
    }
};
