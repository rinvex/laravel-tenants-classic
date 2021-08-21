<?php

declare(strict_types=1);

if (! function_exists('central_domains')) {
    /**
     * Return central domains array.
     *
     * @return array
     */
    function central_domains()
    {
        return array_merge([central_domain()], (array) config('rinvex.tenants.alias_domains'));
    }
}

if (! function_exists('central_domain')) {
    /**
     * Return default central domain.
     *
     * @return array
     */
    function central_domain()
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}

if (! function_exists('central_subdomains')) {
    /**
     * Return central subdomains array.
     *
     * @return array
     */
    function central_subdomains()
    {
        return app('request.tenant') ? collect(central_domains())->map(fn ($centralDomain) => app('request.tenant')->slug.'.'.$centralDomain)->toArray() : [];
    }
}

if (! function_exists('tenant_domains')) {
    /**
     * Return tenant domains array.
     *
     * @return array
     */
    function tenant_domains()
    {
        return app('request.tenant') ? array_merge(central_subdomains(), [app('request.tenant')->domain]) : [];
    }
}
