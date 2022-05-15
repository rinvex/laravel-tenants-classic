<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Traits;

use Rinvex\Tenants\Scopes\TenantableChildScope;

trait TenantableChild
{
    abstract public function getRelationshipToTenantable(): string;

    public static function bootTenantableChild()
    {
        if (app('request.tenant')) {
            static::addGlobalScope('tenantable-child', new TenantableChildScope());
        }
    }
}
