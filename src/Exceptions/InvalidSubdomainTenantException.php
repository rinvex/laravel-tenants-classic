<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Exceptions;

class InvalidSubdomainTenantException extends AbstractTenantException
{
    public function __construct(string $host)
    {
        parent::__construct("Host ${host} is invalid subdomain!");
    }
}
