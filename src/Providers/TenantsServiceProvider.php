<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Providers;

use Rinvex\Tenants\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Tenants\Console\Commands\MigrateCommand;
use Rinvex\Tenants\Console\Commands\PublishCommand;
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
        MigrateCommand::class => 'command.rinvex.tenants.migrate',
        PublishCommand::class => 'command.rinvex.tenants.publish',
        RollbackCommand::class => 'command.rinvex.tenants.rollback',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.tenants');

        // Bind eloquent models to IoC container
        $this->app->singleton('rinvex.tenants.tenant', $tenantModel = $this->app['config']['rinvex.tenants.models.tenant']);
        $tenantModel === Tenant::class || $this->app->alias('rinvex.tenants.tenant', Tenant::class);

        // Register console commands
        ! $this->app->runningInConsole() || $this->registerCommands();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-tenants');
        $this->publishesMigrations('rinvex/laravel-tenants');
        ! $this->autoloadMigrations('rinvex/tenants') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
