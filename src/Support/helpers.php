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
        return array_merge([parse_url(config('app.url'), PHP_URL_HOST)], (array) config('rinvex.tenants.central_domains'));
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
        return app('request.tenant') ? [app('request.tenant')->slug.'.'.parse_url(config('app.url'), PHP_URL_HOST), app('request.tenant')->domain] : [];
    }
}
