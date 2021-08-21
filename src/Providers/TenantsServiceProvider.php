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

        // Resolve active tenant
        $this->resolveActiveTenant();
    }

    /**
     * Resolve active tenant.
     *
     * @return void
     */
    public function resolveActiveTenant()
    {
        $centralDomains = central_domains();
        $domain = $this->app['request']->getHost();

        // Resolve and register tenant into service container
        $this->app->singleton('request.tenant', fn () => ! in_array($domain, $centralDomains) ? config('rinvex.tenants.resolver')::resolve() : null);

        // Dynamically change session domain config on the fly
        if (in_array($domain, array_merge([optional($this->app['request.tenant'])->domain], config('rinvex.tenants.alias_domains')))) {
            config()->set('session.domain', '.'.$domain);
        } else if (Str::endsWith($domain, config('rinvex.tenants.alias_domains'))) {
            $domain = collect(config('rinvex.tenants.alias_domains'))->first(fn ($alias) => Str::endsWith($domain, $alias));
            config()->set('session.domain', '.'.$domain);
        }
    }
}
