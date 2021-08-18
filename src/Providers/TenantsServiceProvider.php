<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Providers;

use Illuminate\Support\Str;
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
        $this->registerModels([
            'rinvex.tenants.tenant' => Tenant::class,
        ]);

        // Register console commands
        $this->registerCommands($this->commands);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-tenants');
        $this->publishesMigrations('rinvex/laravel-tenants');
        ! $this->autoloadMigrations('rinvex/laravel-tenants') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Resolve and register tenant into service container
        $this->app->singleton('request.tenant', fn () => ! in_array($this->app['request']->getHost(), central_domains()) ? config('rinvex.tenants.resolver')::resolve() : null);

        // Dynamically change session domain config on the fly, if current requested host is not a central domain or a central subdomain
        if (in_array($domain = $this->app->request->getHost(), array_merge(central_domains(), tenant_domains())) && ! Str::endsWith($domain, central_domains())) {
            config()->set('session.domain', '.'.$domain);
        }
    }
}
