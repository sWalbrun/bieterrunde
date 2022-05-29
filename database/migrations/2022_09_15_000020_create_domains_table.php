<?php

declare(strict_types=1);

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->increments('id');
            $table->string('domain', 255)->unique();
            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on(Tenant::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
}
