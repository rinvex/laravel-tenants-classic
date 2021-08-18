<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Scopes;

use Rinvex\Tenants\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TenantableScope implements Scope
{
    /**
     * Tenant model instance.
     *
     * @var \Rinvex\Tenants\Models\Tenant
     */
    protected $tenant;

    /**
     * Create a new Tenant Scope instance.
     *
     * @param \Rinvex\Tenants\Models\Tenant $tenant
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereHas('tenants', function (Builder $builder) {
            $builder->where($key = $this->tenant->getKeyName(), $this->tenant->{$key})->where('is_active', true);
        });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutTenants', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
