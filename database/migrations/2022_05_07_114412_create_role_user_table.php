<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('roles.connection');
        $table = config('roles.roleUserTable');
        $rolesTable = config('roles.rolesTable');
        $tableCheck = Schema::connection($connection)->hasTable($table);

        if (!$tableCheck) {
            Schema::connection($connection)->create($table, function (Blueprint $table) use ($rolesTable) {
                $table->increments('id')->unsigned();
                $table->integer('role_id')->unsigned()->index();
                $table->foreign('role_id')->references('id')->on($rolesTable)->onDelete('cascade');
                $table->unsignedBigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on(User::TABLE)->onDelete('cascade');
                $table->timestamp(User::COL_CREATED_AT)->nullable();
                $table->timestamp(User::COL_UPDATED_AT)->nullable();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('roles.connection');
        $table = config('roles.roleUserTable');
        Schema::connection($connection)->dropIfExists($table);
    }
}
