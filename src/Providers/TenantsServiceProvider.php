<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Providers;

use Exception;
use Rinvex\Tenants\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Tenants\Console\Commands\MigrateCommand;
use Rinvex\Tenants\Console\Commands\PublishCommand;
use Illuminate\Database\Eloquent\Relations\Relation;
use Rinvex\Tenants\Console\Commands\RollbackCommand;

class TenantsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class,
        PublishCommand::class,
        RollbackCommand::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.tenants');

        // Bind eloquent models to IoC container
        $this->registerModels([
            'rinvex.tenants.tenant' => Tenant::class,
        ]);

        // Register console commands
        $this->commands($this->commands);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Register paths to be published by the publish command.
        $this->publishConfigFrom(__DIR__.'/../../config/config.php', 'rinvex/tenants');
        $this->publishMigrationsFrom(__DIR__.'/../../database/migrations', 'rinvex/tenants');

        ! $this->app['config']['rinvex.tenants.autoload_migrations'] || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Resolve active tenant
        $this->resolveActiveTenant();

        // Map relations
        Relation::morphMap([
            'tenant' => config('rinvex.tenants.models.tenant'),
        ]);
    }

    /**
     * Resolve active tenant.
     *
     * @return void
     */
    protected function resolveActiveTenant()
    {
        $tenant = null;

        try {
            // Just check if we have DB connection! This is to avoid
            // exceptions on new projects before configuring database options
            DB::connection()->getPdo();

            if (! array_key_exists($this->app['request']->getHost(), config('app.domains')) && Schema::hasTable(config('rinvex.tenants.tables.tenants'))) {
                $tenant = config('rinvex.tenants.resolver')::resolve();
            }
        } catch (Exception $e) {
            // Be quiet! Do not do or say anything!!
        }

        // Resolve and register tenant into service container
        $this->app->singleton('request.tenant', fn () => $tenant);
    }
}
