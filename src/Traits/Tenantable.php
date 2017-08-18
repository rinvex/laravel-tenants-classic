<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Rinvex\Tenantable\Exceptions\ModelNotFoundForTenantException;

trait Tenantable
{
    /**
     * The Queued tenants.
     *
     * @var array
     */
    protected $queuedTenants = [];

    /**
     * Register a created model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function created($callback);

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
     * @param string      $related
     * @param string      $name
     * @param string|null $table
     * @param string|null $foreignKey
     * @param string|null $otherKey
     * @param bool        $inverse
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    abstract public function morphToMany($related, $name, $table = null, $foreignKey = null, $otherKey = null, $inverse = false);

    /**
     * Get tenant class name.
     *
     * @return string
     */
    public static function getTenantClassName(): string
    {
        return Tenant::class;
    }

    /**
     * Get all attached tenants to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tenants(): MorphToMany
    {
        return $this->morphToMany(static::getTenantClassName(), 'tenantable', config('rinvex.tenantable.tables.tenantables'), 'tenantable_id', 'tenant_id')
                    ->withTimestamps();
    }

    /**
     * Attach the given tenant(s) to the model.
     *
     * @param int|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     *
     * @return void
     */
    public function setTenantsAttribute($tenants)
    {
        if (! $this->exists) {
            $this->queuedTenants = $tenants;

            return;
        }

        $this->attachTenants($tenants);
    }

    /**
     * Boot the tenantable trait for the model.
     *
     * @return void
     */
    public static function bootTenantable()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if ($tenant = config('rinvex.tenantable.tenant')) {
                $builder->whereHas('tenants', function (Builder $builder) use ($tenant) {
                    $key = $tenant instanceof Model ? $tenant->getKeyName() : (is_int($tenant) ? 'id' : 'slug');
                    $value = $tenant instanceof Model ? $tenant->$key : $tenant;
                    $builder->where($key, $value);
                });
            }
        });

        static::created(function (Model $tenantableModel) {
            if ($tenantableModel->queuedTenants) {
                $tenantableModel->attachTenants($tenantableModel->queuedTenants);

                $tenantableModel->queuedTenants = [];
            }
        });

        static::deleted(function (Model $tenantableModel) {
            $tenantableModel->tenants()->detach();
        });
    }

    /**
     * Attach the given tenant(s) to the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     *
     * @return void
     */
    public function attachTenants($tenants)
    {
        $this->tenants()->syncWithoutDetaching($tenants);
    }

    /**
     * Remove the given tenant(s) from the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     *
     * @return void
     */
    public function detachTenants($tenants = null)
    {
        $this->tenants()->detach($tenants);
    }

    /**
     * Get the tenant list.
     *
     * @param string $keyColumn
     *
     * @return array
     */
    public function tenantList(string $keyColumn = 'id'): array
    {
        return $this->tenants()->pluck('name', $keyColumn)->toArray();
    }

    /**
     * Filter tenants with group.
     *
     * @param string|null $group
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function tenantsWithGroup(string $group = null): Collection
    {
        return $this->tenants->filter(function (Tenant $tenant) use ($group) {
            return $tenant->group === $group;
        });
    }

    /**
     * Scope query with all the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $builder
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     * @param string                                                  $column
     * @param string                                                  $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllTenants(Builder $builder, $tenants, string $column = 'id', string $group = null): Builder
    {
        $tenants = $tenants instanceof Model ? $tenants->{$tenants->getKey()} : ($tenants instanceof Collection ? $tenants->pluck($column)->toArray() : $tenants);

        collect($tenants)->each(function ($tenant) use ($builder, $column, $group) {
            $builder->whereHas('tenants', function (Builder $builder) use ($tenant, $column, $group) {
                return $builder->where($column, $tenant)->when($group, function (Builder $builder) use ($group) {
                    return $builder->where('group', $group);
                });
            });
        });

        return $builder;
    }

    /**
     * Scope query with any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $builder
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     * @param string                                                  $column
     * @param string                                                  $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyTenants(Builder $builder, $tenants, string $column = 'id', string $group = null): Builder
    {
        $tenants = $tenants instanceof Model ? $tenants->{$tenants->getKey()} : ($tenants instanceof Collection ? $tenants->pluck($column)->toArray() : $tenants);

        return $builder->whereHas('tenants', function (Builder $builder) use ($tenants, $column, $group) {
            $builder->whereIn($column, (array) $tenants)->when($group, function (Builder $builder) use ($group) {
                return $builder->where('group', $group);
            });
        });
    }

    /**
     * Scope query with any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $builder
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     * @param string                                                  $column
     * @param string                                                  $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTenants(Builder $builder, $tenants, string $column = 'id', string $group = null): Builder
    {
        return static::scopeWithAnyTenants($builder, $tenants, $column, $group);
    }

    /**
     * Scope query without any of the given tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $builder
     * @param int|string|array|\ArrayAccess|\Rinvex\Tenantable\Models\Tenant $tenants
     * @param string                                                  $column
     * @param string                                                  $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutTenants(Builder $builder, $tenants, string $column = 'id', string $group = null): Builder
    {
        $tenants = $tenants instanceof Model ? $tenants->{$tenants->getKey()} : ($tenants instanceof Collection ? $tenants->pluck($column)->toArray() : $tenants);

        return $builder->whereDoesntHave('tenants', function (Builder $builder) use ($tenants, $column, $group) {
            $builder->whereIn($column, (array) $tenants)->when($group, function (Builder $builder) use ($group) {
                return $builder->where('group', $group);
            });
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
     * Returns a new query builder without any of the tenant scopes applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forAllTenants()
    {
        return (new static())->newQuery()->withoutGlobalScopes(['tenant']);
    }

    /**
     * Override the default findOrFail method so that we can re-throw
     * a more useful exception. Otherwise it can be very confusing
     * why queries don't work because of tenant scoping issues.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @throws \Rinvex\Tenantable\Exceptions\ModelNotFoundForTenantException
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public static function findOrFail($id, $columns = ['*'])
    {
        try {
            return static::query()->findOrFail($id, $columns);
        } catch (ModelNotFoundException $e) {
            // If it DOES exist, just not for this tenant, throw a nicer exception
            if (! is_null(static::forAllTenants()->find($id, $columns))) {
                throw (new ModelNotFoundForTenantException())->setModel(get_called_class(), [$id]);
            }

            throw $e;
        }
    }
}
