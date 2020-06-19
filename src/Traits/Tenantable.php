<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Traits;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rinvex\Tenants\Exceptions\ModelNotFoundForTenantException;

trait Tenantable
{
    /**
     * Register a saved model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function saved($callback);

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param bool   $inverse
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    abstract public function morphToMany(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $inverse = false
    );

    /**
     * Get all attached tenants to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tenants(): MorphToMany
    {
        return $this->morphToMany(config('rinvex.tenants.models.tenant'), 'tenantable', config('rinvex.tenants.tables.tenantables'), 'tenantable_id', 'tenant_id')
                    ->withTimestamps();
    }

    /**
     * Attach the given tenant(s) to the model.
     *
     * @param mixed $tenants
     *
     * @return void
     */
    public function setTenantsAttribute($tenants): void
    {
        static::saved(function (self $model) use ($tenants) {
            $model->syncTenants($tenants);
        });
    }

    /**
     * Boot the tenantable trait for the model.
     *
     * @return void
     */
    public static function bootTenantable()
    {
        if ($tenant = app('request.tenant')) {
            static::addGlobalScope('tenantable', function (Builder $builder) use ($tenant) {
                $builder->whereHas('tenants', function (Builder $builder) use ($tenant) {
                    $key = $tenant instanceof Model ? $tenant->getKeyName() : (is_int($tenant) ? 'id' : 'slug');
                    $value = $tenant instanceof Model ? $tenant->{$key} : $tenant;
                    $builder->where($key, $value);
                });
            });

            static::saved(function (self $model) use ($tenant) {
                $model->attachTenants($tenant);
            });
        }

        static::deleted(function (self $model) {
            $model->tenants()->detach();
        });
    }

    /**
     * Returns a new query builder without any of the tenant scopes applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forAllTenants()
    {
        return (new static())->newQuery()->withoutGlobalScopes(['tenantable']);
    }

    /**
     * Override the default findOrFail method so that we can re-throw
     * a more useful exception. Otherwise it can be very confusing
     * why queries don't work because of tenant scoping issues.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @throws \Rinvex\Tenants\Exceptions\ModelNotFoundForTenantException
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public static function findOrFail($id, $columns = ['*'])
    {
        try {
            return static::query()->findOrFail($id, $columns);
        } catch (ModelNotFoundException $exception) {
            // If it DOES exist, just not for this tenant, throw a nicer exception
            if (! is_null(static::forAllTenants()->find($id, $columns))) {
                throw (new ModelNotFoundForTenantException())->setModel(static::class, [$id]);
            }

            throw $exception;
        }
    }

    /**
     * Scope query with all the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed                                 $tenants
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllTenants(Builder $builder, $tenants): Builder
    {
        $tenants = $this->prepareTenantIds($tenants);

        collect($tenants)->each(function ($tenant) use ($builder) {
            $builder->whereHas('tenants', function (Builder $builder) use ($tenant) {
                return $builder->where('id', $tenant);
            });
        });

        return $builder;
    }

    /**
     * Scope query with any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed                                 $tenants
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyTenants(Builder $builder, $tenants): Builder
    {
        $tenants = $this->prepareTenantIds($tenants);

        return $builder->whereHas('tenants', function (Builder $builder) use ($tenants) {
            $builder->whereIn('id', $tenants);
        });
    }

    /**
     * Scope query with any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed                                 $tenants
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTenants(Builder $builder, $tenants): Builder
    {
        return static::scopeWithAnyTenants($builder, $tenants);
    }

    /**
     * Scope query without any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param mixed                                 $tenants
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutTenants(Builder $builder, $tenants): Builder
    {
        $tenants = $this->prepareTenantIds($tenants);

        return $builder->whereDoesntHave('tenants', function (Builder $builder) use ($tenants) {
            $builder->whereIn('id', $tenants);
        });
    }

    /**
     * Scope query without any tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAnyTenants(Builder $builder): Builder
    {
        return $builder->doesntHave('tenants');
    }

    /**
     * Determine if the model has any of the given tenants.
     *
     * @param mixed $tenants
     *
     * @return bool
     */
    public function hasTenants($tenants): bool
    {
        $tenants = $this->prepareTenantIds($tenants);

        return ! $this->tenants->pluck('id')->intersect($tenants)->isEmpty();
    }

    /**
     * Determine if the model has any the given tenants.
     *
     * @param mixed $tenants
     *
     * @return bool
     */
    public function hasAnyTenants($tenants): bool
    {
        return static::hasTenants($tenants);
    }

    /**
     * Determine if the model has all of the given tenants.
     *
     * @param mixed $tenants
     *
     * @return bool
     */
    public function hasAllTenants($tenants): bool
    {
        $tenants = $this->prepareTenantIds($tenants);

        return collect($tenants)->diff($this->tenants->pluck('id'))->isEmpty();
    }

    /**
     * Sync model tenants.
     *
     * @param mixed $tenants
     * @param bool  $detaching
     *
     * @return $this
     */
    public function syncTenants($tenants, bool $detaching = true)
    {
        // Find tenants
        $tenants = $this->prepareTenantIds($tenants);

        // Sync model tenants
        $this->tenants()->sync($tenants, $detaching);

        return $this;
    }

    /**
     * Attach model tenants.
     *
     * @param mixed $tenants
     *
     * @return $this
     */
    public function attachTenants($tenants)
    {
        return $this->syncTenants($tenants, false);
    }

    /**
     * Detach model tenants.
     *
     * @param mixed $tenants
     *
     * @return $this
     */
    public function detachTenants($tenants = null)
    {
        $tenants = ! is_null($tenants) ? $this->prepareTenantIds($tenants) : null;

        // Sync model tenants
        $this->tenants()->detach($tenants);

        return $this;
    }

    /**
     * Prepare tenant IDs.
     *
     * @param mixed $tenants
     *
     * @return array
     */
    protected function prepareTenantIds($tenants): array
    {
        // Convert collection to plain array
        if ($tenants instanceof BaseCollection && is_string($tenants->first())) {
            $tenants = $tenants->toArray();
        }

        // Find tenants by their ids
        if (is_numeric($tenants) || (is_array($tenants) && is_numeric(Arr::first($tenants)))) {
            return array_map('intval', (array) $tenants);
        }

        // Find tenants by their slugs
        if (is_string($tenants) || (is_array($tenants) && is_string(Arr::first($tenants)))) {
            $tenants = app('rinvex.tenants.tenant')->whereIn('slug', $tenants)->get()->pluck('id');
        }

        if ($tenants instanceof Model) {
            return [$tenants->getKey()];
        }

        if ($tenants instanceof Collection) {
            return $tenants->modelKeys();
        }

        if ($tenants instanceof BaseCollection) {
            return $tenants->toArray();
        }

        return (array) $tenants;
    }
}
