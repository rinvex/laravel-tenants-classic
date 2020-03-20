<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.tenants.tables.tenantables'), function (Blueprint $table) {
            // Columns
            $table->bigInteger('tenant_id')->unsigned();
            $table->morphs('tenantable');
            $table->timestamps();

            // Indexes
            $table->unique(['tenant_id', 'tenantable_id', 'tenantable_type'], 'tenantables_ids_type_unique');
            $table->foreign('tenant_id')->references('id')->on(config('rinvex.tenants.tables.tenants'))
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.tenants.tables.tenantables'));
    }
}
