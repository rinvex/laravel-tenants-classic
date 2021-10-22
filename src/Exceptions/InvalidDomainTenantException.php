<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Exceptions;

class InvalidDomainTenantException extends AbstractTenantException
{
    public function __construct($domain)
    {
        parent::__construct("Tenant not found for domain {$domain}");
    }
}
