<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Providers;

use Rinvex\Tenantable\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Rinvex\Tenantable\Console\Commands\MigrateCommand;

class TenantableServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        MigrateCommand::class => 'command.rinvex.tenantable.migrate',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.tenantable');

        // Bind eloquent models to IoC container
        $this->app->singleton('rinvex.tenantable.tenant', function ($app) {
            return new $app['config']['rinvex.tenantable.models.tenant']();
        });
        $this->app->alias('rinvex.tenantable.tenant', Tenant::class);

        // Register artisan commands
        foreach ($this->commands as $key => $value) {
            $this->app->singleton($value, function ($app) use ($key) {
                return new $key();
            });
        }

        $this->commands(array_values($this->commands));
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Load migrations
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

            // Publish Resources
            $this->publishResources();
        }
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    protected function publishResources()
    {
        $this->publishes([realpath(__DIR__.'/../../config/config.php') => config_path('rinvex.tenantable.php')], 'rinvex-tenantable-config');
        $this->publishes([realpath(__DIR__.'/../../database/migrations') => database_path('migrations')], 'rinvex-tenantable-migrations');
    }
}
