<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('rinvex.tenants.tables.tenants'), function (Blueprint $table) {
            // Columns
            $table->increments('id');
            $table->string('slug');
            $table->string('domain')->nullable();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('language_code', 2);
            $table->string('country_code', 2);
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->date('launch_date')->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique('slug');
            $table->unique('domain');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('rinvex.tenants.tables.tenants'));
    }
}
