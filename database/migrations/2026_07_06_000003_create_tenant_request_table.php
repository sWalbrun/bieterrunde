<?php

use App\Enums\EnumTenantRequestStatus;
use App\Models\TenantRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(TenantRequest::TABLE, function (Blueprint $table) {
            $table->id();
            $table->string(TenantRequest::COL_NAME);
            $table->string(TenantRequest::COL_EMAIL);
            $table->string(TenantRequest::COL_SOLAWI_NAME);
            $table->string(TenantRequest::COL_WEBSITE_URL)->nullable();
            $table->string(TenantRequest::COL_STATUS, 20)->default(EnumTenantRequestStatus::PENDING->value);
            $table->string(TenantRequest::COL_FK_TENANT, 125)->nullable();
            $table->timestamp(TenantRequest::COL_CREATED_AT)->nullable();
            $table->timestamp(TenantRequest::COL_UPDATED_AT)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(TenantRequest::TABLE);
    }
};
