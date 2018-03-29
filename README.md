# Rinvex Tenants

**Rinvex Tenants** is a contextually intelligent polymorphic Laravel package, for single db multi-tenancy. You can completely isolate tenants data with ease using the same database, with full power and control over what data to be centrally shared, and what to be tenant related and therefore isolated from others.

[![Packagist](https://img.shields.io/packagist/v/rinvex/tenants.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/tenants)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/tenants.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/tenants/)
[![Code Climate](https://img.shields.io/codeclimate/github/rinvex/tenants.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/rinvex/tenants)
[![Travis](https://img.shields.io/travis/rinvex/tenants.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/tenants)
[![StyleCI](https://styleci.io/repos/87875339/shield)](https://styleci.io/repos/87875339)
[![License](https://img.shields.io/packagist/l/rinvex/tenants.svg?label=License&style=flat-square)](https://github.com/rinvex/tenants/blob/develop/LICENSE)


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/tenants
    ```

2. Execute migrations via the following command:
    ```
    php artisan rinvex:migrate:tenants
    ```

3. Done!


## Usage

**Rinvex Tenants** is developed with the concept that every tenantable model can be attached to multiple tenants at the same time, so you don't need special column in your model database table to specify the tenant it belongs to, tenant relationships simply stored in a separate central table.

To add tenants support to your eloquent models simply use `\Rinvex\Tenants\Traits\Tenantable` trait.

### Manage your tenants

Nothing special here, just normal [Eloquent](https://laravel.com/docs/master/eloquent) model stuff:

```php
// Create a new tenant
app('rinvex.tenants.tenant')->create([
    'title' => 'ACME Inc.',
    'slug' => 'acme',
    'owner_id' => '1',
    'owner_type' => 'manager',
    'email' => 'owner@acme.inc',
    'language_code' => 'en',
    'country_code' => 'us',
]);

// Get existing tenant by id
$tenant = app('rinvex.tenants.tenant')->find(1);
```

> **Notes:** since **Rinvex Tenants** extends and utilizes other awesome packages, checkout the following documentations for further details:
> - Translatable out of the box using [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable)
> - Automatic Slugging using [`spatie/laravel-sluggable`](https://github.com/spatie/laravel-sluggable)

### Activate Your Tenant

**Rinvex Tenants** is stateless, which means you have to set the active tenant on every request, therefore it will only scope that specific request.

Make sure to activate your tenants in such a way that it happens on every request, and before you need Models scoped, like in a middleware or as part of a stateless authentication method like OAuth.

By default we set the active tenant by setting a runtime config value, [the normal way](https://laravel.com/docs/master/configuration#accessing-configuration-values):

```php
config(['rinvex.tenants.active' => 1]);
```

You can pass either tenant id, slug, or instance. This package is smart enough to figure it out.

Note that you can only activate one tenant at a time, even if your resources belongs to multiple tenants, only one tenant could be active. You still have the ability to change the active tenant at any point of the request, but note that it will have scoping effect only on those models requested after that change, while any other models requested at an earlier stage of the request will be scoped with the previous tenant, or not scoped at all (according to your logic).

To deactivate your tenant and stop scoping by it, simply unset that runtime config value as follows:

```php
config(['rinvex.tenants.active' => null]);
```

### Querying Tenant scoped Models

After you've added tenants, all queries against a Model which uses `\Rinvex\Tenants\Traits\Tenantable` will be scoped automatically:

```php
// This will only include Models belonging to the active tenant
$tenantProducts = \App\Models\Product::all();

// This will fail with a `ModelNotFoundForTenantException` if it belongs to the wrong tenant (if active tenant is 1 for example)
$product = \App\Models\Product::find(2);
```

If you need to query across all tenants, you can use `forAllTenants()` method:

```php
// Will include results from ALL tenants, just for this query
$allTenantProducts = \App\Models\Product::forAllTenants()->get();
```

Under the hood, **Rinvex Tenants** uses Laravel's [anonymous global scopes](https://laravel.com/docs/master/eloquent#global-scopes), which means if you are scoping by active tenant, and you want to exclude one single query, you can do so:

```php
// Will NOT be scoped, and will return results from ALL tenants, just for this query
$allTenantProducts = \App\Models\Product::withoutGlobalScope('tenantable')->get();
```

> **Notes:**
> - When you are developing multi-tenancy applications, it can be confusing sometimes why you keep getting `ModelNotFound` exceptions for rows that **DO** exist, because they belong to the wrong tenant.
> - **Rinvex Tenants** will catch those exceptions, and re-throw them as `ModelNotFoundForTenantException`, to help you out 🙂

### Manage your tenantable model

The API is intutive and very straightfarwad, so let's give it a quick look:

```php
// Get instance of your model
$product = new \App\Models\Product::find(1);

// Get attached tenants collection
$product->tenants;

// Get attached tenants query builder
$product->tenants();
```

You can attach tenants in various ways:

```php
// Single tenant id
$product->attachTenants(1);

// Multiple tenant IDs array
$product->attachTenants([1, 2, 5]);

// Multiple tenant IDs collection
$product->attachTenants(collect([1, 2, 5]));

// Single tenant model instance
$tenantInstance = app('rinvex.tenants.tenant')->first();
$product->attachTenants($tenantInstance);

// Single tenant slug
$product->attachTenants('test-tenant');

// Multiple tenant slugs array
$product->attachTenants(['first-tenant', 'second-tenant']);

// Multiple tenant slugs collection
$product->attachTenants(collect(['first-tenant', 'second-tenant']));

// Multiple tenant model instances
$tenantInstances = app('rinvex.tenants.tenant')->whereIn('id', [1, 2, 5])->get();
$product->attachTenants($tenantInstances);
```

> **Notes:** 
> - The `attachTenants()` method attach the given tenants to the model without touching the currently attached tenants, while there's the `syncTenants()` method that can detach any records that's not in the given items, this method takes a second optional boolean parameter that's set detaching flag to `true` or `false`.
> - To detach model tenants you can use the `detachTenants()` method, which uses **exactly** the same signature as the `attachTenants()` method, with additional feature of detaching all currently attached tenants by passing null or nothing to that method as follows: `$product->detachTenants();`.

And as you may have expected, you can check if tenants attached:

```php
// Single tenant id
$product->hasAnyTenants(1);

// Multiple tenant IDs array
$product->hasAnyTenants([1, 2, 5]);

// Multiple tenant IDs collection
$product->hasAnyTenants(collect([1, 2, 5]));

// Single tenant model instance
$tenantInstance = app('rinvex.tenants.tenant')->first();
$product->hasAnyTenants($tenantInstance);

// Single tenant slug
$product->hasAnyTenants('test-tenant');

// Multiple tenant slugs array
$product->hasAnyTenants(['first-tenant', 'second-tenant']);

// Multiple tenant slugs collection
$product->hasAnyTenants(collect(['first-tenant', 'second-tenant']));

// Multiple tenant model instances
$tenantInstances = app('rinvex.tenants.tenant')->whereIn('id', [1, 2, 5])->get();
$product->hasAnyTenants($tenantInstances);
```

> **Notes:** 
> - The `hasAnyTenants()` method check if **ANY** of the given tenants are attached to the model. It returns boolean `true` or `false` as a result.
> - Similarly the `hasAllTenants()` method uses **exactly** the same signature as the `hasAnyTenants()` method, but it behaves differently and performs a strict comparison to check if **ALL** of the given tenants are attached.

### Advanced Usage

#### Generate Tenant Slugs

**Rinvex Tenants** auto generates slugs and auto detect and insert default translation for you if not provided, but you still can pass it explicitly through normal eloquent `create` method, as follows:

```php
app('rinvex.tenants.tenant')->create(['title' => ['en' => 'My New Tenant'], 'slug' => 'custom-tenant-slug']);
```

> **Note:** Check **[Sluggable](https://github.com/spatie/laravel-sluggable)** package for further details.

#### Smart Parameter Detection

**Rinvex Tenants** methods that accept list of tenants are smart enough to handle almost all kinds of inputs as you've seen in the above examples. It will check input type and behave accordingly. 

#### Retrieve All Models Attached To The Tenant

You may encounter a situation where you need to get all models attached to certain tenant, you do so with ease as follows:

```php
$tenant = app('rinvex.tenants.tenant')->find(1);
$tenant->entries(\App\Models\Product::class);
```

#### Query Scopes

Yes, **Rinvex Tenants** shipped with few awesome query scopes for your convenience, usage example:

```php
// Single tenant id
$product->withAnyTenants(1)->get();

// Multiple tenant IDs array
$product->withAnyTenants([1, 2, 5])->get();

// Multiple tenant IDs collection
$product->withAnyTenants(collect([1, 2, 5]))->get();

// Single tenant model instance
$tenantInstance = app('rinvex.tenants.tenant')->first();
$product->withAnyTenants($tenantInstance)->get();

// Single tenant slug
$product->withAnyTenants('test-tenant')->get();

// Multiple tenant slugs array
$product->withAnyTenants(['first-tenant', 'second-tenant'])->get();

// Multiple tenant slugs collection
$product->withAnyTenants(collect(['first-tenant', 'second-tenant']))->get();

// Multiple tenant model instances
$tenantInstances = app('rinvex.tenants.tenant')->whereIn('id', [1, 2, 5])->get();
$product->withAnyTenants($tenantInstances)->get();
```

> **Notes:**
> - The `withAnyTenants()` scope finds products with **ANY** attached tenants of the given. It returns normally a query builder, so you can chain it or call `get()` method for example to execute and get results.
> - Similarly there's few other scopes like `withAllTenants()` that finds products with **ALL** attached tenants of the given, `withoutTenants()` which finds products without **ANY** attached tenants of the given, and lastly `withoutAnyTenants()` which find products without **ANY** attached tenants at all. All scopes are created equal, with same signature, and returns query builder.

#### Tenant Translations

Manage tenant translations with ease as follows:

```php
$tenant = app('rinvex.tenants.tenant')->find(1);

// Update title translations
$tenant->setTranslation('title', 'en', 'New English Tenant Title')->save();

// Alternatively you can use default eloquent update
$tenant->update([
    'title' => [
        'en' => 'New Tenant',
        'ar' => 'مستأجر جديد',
    ],
]);

// Get single tenant translation
$tenant->getTranslation('title', 'en');

// Get all tenant translations
$tenant->getTranslations('title');

// Get tenant title in default locale
$tenant->title;
```

> **Note:** Check **[Translatable](https://github.com/spatie/laravel-translatable)** package for further details.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat on Slack](http://chat.rinvex.com)
- [Help on Email](mailto:help@rinvex.com)
- [Follow on Twitter](https://twitter.com/rinvex)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Feature Requests](CONTRIBUTING.md#feature-requests)
- [Git Flow](CONTRIBUTING.md#git-flow)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to [help@rinvex.com](help@rinvex.com). All security vulnerabilities will be promptly addressed.


## About Rinvex

Rinvex is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Alexandria, Egypt since June 2016. We believe that our drive The Value, The Reach, and The Impact is what differentiates us and unleash the endless possibilities of our philosophy through the power of software. We like to call it Innovation At The Speed Of Life. That’s how we do our share of advancing humanity.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016-2018 Rinvex LLC, Some rights reserved.
