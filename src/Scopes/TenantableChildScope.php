<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TenantableChildScope implements Scope
{
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
        $builder->whereHas($builder->getModel()->getRelationshipToTenantable());
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
        $builder->macro('withoutTenantables', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
