<?php

use App\Enums\EnumContributionGroup;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(User::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string(User::COL_NAME);
            $table->string(User::COL_EMAIL)->unique();
            $table->timestamp(User::COL_EMAIL_VERIFIED_AT)->nullable();
            $table->string(User::COL_PASSWORD);
            $table->rememberToken();
            $table->enum(User::COL_CONTRIBUTION_GROUP, EnumContributionGroup::getValues())->nullable();
            $table->time(User::COL_JOIN_DATE)->nullable();
            $table->time(User::COL_EXIT_DATE)->nullable();
            $table->integer(User::COL_COUNT_SHARES)->unsigned()->nullable();
            $table->foreignId(User::COL_CURRENT_TEAM_ID)->nullable();
            $table->string(User::COL_PROFILE_PHOTO_PATH, 2048)->nullable();

            $table->timestamp(User::COL_CREATED_AT)->nullable();
            $table->timestamp(User::COL_UPDATED_AT)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(User::TABLE);
    }
}
