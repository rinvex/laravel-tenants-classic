# Rinvex Tenants

**Rinvex Tenants** is a contextually intelligent polymorphic Laravel package, for single db multi-tenancy. You can completely isolate tenants data with ease using the same database, with full power and control over what data to be centrally shared, and what to be tenant related and therefore isolated from others.

[![Packagist](https://img.shields.io/packagist/v/rinvex/laravel-tenants.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/laravel-tenants)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/laravel-tenants.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/laravel-tenants/)
[![Travis](https://img.shields.io/travis/rinvex/laravel-tenants.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/laravel-tenants)
[![StyleCI](https://styleci.io/repos/87875339/shield)](https://styleci.io/repos/87875339)
[![License](https://img.shields.io/packagist/l/rinvex/laravel-tenants.svg?label=License&style=flat-square)](https://github.com/rinvex/laravel-tenants/blob/develop/LICENSE)


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/laravel-tenants
    ```

2. Publish resources (migrations and config files):
    ```shell
    php artisan rinvex:publish:tenants
    ```

3. Execute migrations via the following command:
    ```shell
    php artisan rinvex:migrate:tenants
    ```

4. Done!


## Usage

**Rinvex Tenants** is developed with the concept that every tenantable model can be attached to multiple tenants at the same time, so you don't need special column in your model database table to specify the tenant it belongs to, tenant relationships simply stored in a separate central table.

### Scope Queries

To scope your queries correctly, apply the `\Rinvex\Tenants\Traits\Tenantable` trait on primary models. This will ensure that all calls to your parent models are scoped to the current tenant, and that calls to their child relations are scoped through the parent relationships.

```php
namespace App\Models;

use App\Models\Feature;
use Rinvex\Tenants\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Tenantable;

    public function features()
    {
        return $this->hasMany(Feature::class);
    }
}
```

#### Scope Child Model Queries

If you have child models, like product features, and these features belongs to tenantable products via a relationship, you may need to scope these feature model queries as well. For that, you need to apply the `\Rinvex\Tenants\Traits\TenantableChild` trait on your child models, and define a new method `getRelationshipToTenantable` that returns a string of the parent relationship. Check the following example.

```php
namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Tenants\Traits\TenantableChild;

class Feature extends Model
{
    use TenantableChild;

    public function getRelationshipToTenantable(): string
    {
        return 'product';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
```

And this will automatically scope the all `App\Models\Feature::class` queries to the current tenant. Note that the limitation of this is that you need to be able to define a relationship to a primary model, so if you need to do this on deeper level of children hierarchy, like a `App\Models\Discount::class` model that belongs to `App\Models\Feature::class` which belongs to `App\Models\Product::class` which belongs to a Tenant `Rinvex\Tenants\Models\Tenant::class`, you need to define some strange relationship. Laravel supports HasOneThrough, but not BelongsToThrough, so you'd need to do some hacks around that.

### Manage your tenants

Nothing special here, just normal [Eloquent](https://laravel.com/docs/master/eloquent) model stuff:

```php
// Create a new tenant
app('rinvex.tenants.tenant')->create([
    'name' => 'ACME Inc.',
    'slug' => 'acme',
    'domain' => 'acme.test',
    'email' => 'info@acme.test',
    'language_code' => 'en',
    'country_code' => 'us',
]);

// Get existing tenant by id
$tenant = app('rinvex.tenants.tenant')->find(1);
```

> **Notes:** since **Rinvex Tenants** extends and utilizes other awesome packages, checkout the following documentations for further details:
> - Translatable out of the box using [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable)
> - Automatic Slugging using [`spatie/laravel-sluggable`](https://github.com/spatie/laravel-sluggable)

### Automatic Tenants Registration

Tenants are automatically registered into [Service Container](https://laravel.com/docs/master/container) very early in the request, through service provider `boot` method.

That way you'll have access to the current active tenant before models are loaded, scopes are needed, or traits are booted. That's also earlier than routes registration, and middleware pipeline, so you can assure any resources that needs to be scoped, are correctly scoped.

### Changing Active Tenant

You can easily change current active tenant at any point of the request as follows:

```php
    $tenant = app('rinvex.tenants.tenant')->find(123);
    app()->bind('request.tenant', fn() => $tenant);
```

And to deactivate your tenant and stop scoping by it, simply set the same container service binding to `null` as follows:

```php
    app()->bind('request.tenant', null);
```

> **Notes:**
> - Only one tenant could be active at a time, even if your resources belongs to multiple tenants.
> - You can change the active tenant at any point of the request, but that newly activated tenant will only scope models retrieved after that change, while any other models retrieved at an earlier stage of the request will be scoped with the previous tenant, or not scoped at all (according to your logic).
> - If a resource belongs to multiple tenants, you can switch between tenants by to a different tenant by reinitializing the request. Example: since tenants are currently resolved by domains or subdomains, to switch tenants you'll need to redirect the user the new tenant domain/subdomain, and the currently active tenant will be switched as well as the request will be automatically scoped by the new tenant.

### Default Tenant Resolvers

**Rinvex Tenants** resolve currently active tenant using Resolver Classes. It comes with few default resolvers that you can use, or you can build your own custom resolver to support additional functionality.

Default tenant resolver classes in config options:

```php
    // Tenant Resolver Class:
    // - \Rinvex\Tenants\Http\Resolvers\DomainTenantResolver::class
    // - \Rinvex\Tenants\Http\Resolvers\SubdomainTenantResolver::class
    // - \Rinvex\Tenants\Http\Resolvers\SubdomainOrDomainTenantResolver::class
    'resolver' => \Rinvex\Tenants\Resolvers\SubdomainOrDomainTenantResolver::class,
```

The default tenant resolver used is `SubdomainOrDomainTenantResolver::class`, so this package automatically resolve currently active tenant using both domains and subdomains. You can change that via config options.

### Central Domains

**Rinvex Tenants** supports running your application on multiple domains, we call them central domains. It also supports more sophisticated use cases, but that's out of this package's scope.

For that reason, this package expects you to have the following config option in your `config/app.php`:

```php
    'domains' => [
        'domain.net' => [],
        'example.com' => [],
    ],
```

### Default Domain

The reason you need to add the above config option in the same format, is that it's meant to support more advanced use cases that's not covered by this package. If you need to check some of these use cases proceed to [Cortex Tenants](https://github.com/rinvex/cortex-tenants) which is an application module implementing accessareas concepts, and allows different domains to access different accessareas (i.e. frontarea, adminarea, managerarea, tenantarea ..etc). The baseline here is that you need to add the above config option to your `config/app.php` and specify all your application domains.

You need to add the default domain to the domains list, since this package automatically overrides the default Laravel config option `app.url` with the matched domain, although you may need to write some application logic. Checkout the above mentioned [Cortex Tenants](https://github.com/rinvex/cortex-tenants) module for an example.

### Tenant Domains

Tenants could be accessed via central subdomains (obviously subdomains on central domains), or via their own dedicated domains.

For example if the default domain is `rinvex.com`, and tenant slug is `cortex` then central subdomain will be `cortex.rinvex.com`.

Note that since this package supports multiple central domains, tenants will be accessible via all central subdomains, so if we have another alias central domain `rinvex.net`, you can expect `cortex` to be available on `cortex.rinvex.net` as well.

Tenants can optionally have top level domains of their own too, something like `test-example.com`, which means it's now accessible through three different domains:
- `cortex.rinvex.com`
- `cortex.rinvex.net`
- `test-example.com`

### Session Domain

Since **Rinvex Tenants** supports multiple central and tenant domains, it needs to change the default laravel session configuration on the fly, and that's actually what it does. It will dynamically change `session.domain` config option based on the current request host.

> **Note:** Due to security reasons, accessing the same application through multiple top level domains (.rinvex.com & .rinvex.net) means the user will need to login for each different domain, as their session and cookies are tied to the top level domain. Example: if the user logged in to `cortex.rinvex.com` they will stay logged-in to the top level domain `rinvex.com` and all it's subdomains like `website.rinvex.com`, but they will not be logged in to `cortex.rinvex.net` even if both domains directs to the same application, they will need to login again there. This is a known limitation due to enforced security restrictions by the browser. We may create a workaround in the future, but it's a bit complicated, and involves third-party cookies and CORS, so feel free to send a PR if you have a creative solution. 

### Querying Tenant Scoped Models

After you've added tenants, all queries against a tenantable Model will be scoped automatically:

```php
// This will only include Models belonging to the currently active tenant
$tenantProducts = \App\Models\Product::all();

// This will fail with a `ModelNotFoundForTenantException` if it belongs to the wrong tenant
$product = \App\Models\Product::find(2);
```

If you need to query across all tenants, you can use `forAllTenants()` method:

```php
// Will include results from ALL tenants, just for this query
$allTenantProducts = \App\Models\Product::forAllTenants()->get();
```

Under the hood, **Rinvex Tenants** uses Laravel's [Global Scopes](https://laravel.com/docs/master/eloquent#global-scopes), which means if you are scoping by active tenant, and you want to exclude one single query, you can do so:

```php
// Will NOT be scoped, and will return results from ALL tenants, just for this query
$allTenantProducts = \App\Models\Product::withoutTenants()->get();
```

> **Notes:**
> - When you are developing multi-tenancy applications, it can be confusing sometimes why you keep getting `ModelNotFound` exceptions for rows that **DO** exist, because they belong to the wrong tenant.
> - **Rinvex Tenants** will catch those exceptions, and re-throw them as `ModelNotFoundForTenantException`, to help you out 🙂

### Manage your tenantable model

The API is intutive and very straightforward, so let's give it a quick look:

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
app('rinvex.tenants.tenant')->create(['name' => ['en' => 'My New Tenant'], 'slug' => 'custom-tenant-slug']);
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
$tenant->setTranslation('name', 'en', 'New English Tenant Title')->save();

// Alternatively you can use default eloquent update
$tenant->update([
    'name' => [
        'en' => 'New Tenant',
        'ar' => 'مستأجر جديد',
    ],
]);

// Get single tenant translation
$tenant->getTranslation('name', 'en');

// Get all tenant translations
$tenant->getTranslations('name');

// Get tenant title in default locale
$tenant->name;
```

> **Note:** Check **[Translatable](https://github.com/spatie/laravel-translatable)** package for further details.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat on Slack](https://bit.ly/rinvex-slack)
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

(c) 2016-2022 Rinvex LLC, Some rights reserved.
