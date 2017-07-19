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
    public function up()
    {
        Schema::create(config('rinvex.tenantable.tables.tenantables'), function (Blueprint $table) {
            // Columns
            $table->integer('tenant_id')->unsigned();
            $table->morphs('tenantable');
            $table->timestamps();

            // Indexes
            $table->unique(['tenant_id', 'tenantable_id', 'tenantable_type'], 'tenantables_ids_type_unique');
            $table->foreign('tenant_id')->references('id')->on(config('rinvex.tenantable.tables.tenants'))
                  ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('rinvex.tenantable.tables.tenantables'));
    }
}
