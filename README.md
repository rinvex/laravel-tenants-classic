# Rinvex Tenantable

**Rinvex Tenantable** is a contextually intelligent polymorphic Laravel package, for single db multi-tenancy. You can completely isolate tenants data with ease using the same database, with full power and control over what data to be centrally shared, and what to be tenant related and therefore isolated from others.

[![Packagist](https://img.shields.io/packagist/v/rinvex/tenantable.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/tenantable)
[![VersionEye Dependencies](https://img.shields.io/versioneye/d/php/rinvex:tenantable.svg?label=Dependencies&style=flat-square)](https://www.versioneye.com/php/rinvex:tenantable/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/tenantable.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/tenantable/)
[![Code Climate](https://img.shields.io/codeclimate/github/rinvex/tenantable.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/rinvex/tenantable)
[![Travis](https://img.shields.io/travis/rinvex/tenantable.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/tenantable)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/6ddccc21-ed54-4738-84b5-0ab311c9c1db.svg?label=SensioLabs&style=flat-square)](https://insight.sensiolabs.com/projects/6ddccc21-ed54-4738-84b5-0ab311c9c1db)
[![StyleCI](https://styleci.io/repos/87875339/shield)](https://styleci.io/repos/87875339)
[![License](https://img.shields.io/packagist/l/rinvex/tenantable.svg?label=License&style=flat-square)](https://github.com/rinvex/tenantable/blob/develop/LICENSE)


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/tenantable
    ```

2. Execute migrations via the following command:
    ```
    php artisan migrate --path="vendor/rinvex/tenantable/database/migrations"
    ```

3. Add the following service provider to the `'providers'` array inside `app/config/app.php`:
    ```php
    Rinvex\Tenantable\TenantableServiceProvider::class,
    ```

4. **Optionally** you can publish migrations and config files by running the following commands:
    ```shell
    // Publish migrations
    php artisan vendor:publish --tag="rinvex-tenantable-migrations"

    // Publish config
    php artisan vendor:publish --tag="rinvex-tenantable-config"
    ```

5. Done!


## Usage

**Rinvex Tenantable** assumes that you have `tenant_id` column on all of your tenant scoped tables that references which tenant each row belongs to, and it's recommended to add foreign key constraint for that column that reference `tenants` table for data integrity.

### Create Your Model

Simply create a new eloquent model, and use `Tenantable` trait:
```php
namespace App;

use Rinvex\Tenantable\Tenant;
use Rinvex\Tenantable\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use Tenantable;

    public function tenants(): MorphToMany
    {
        return $this->morphToMany(Tenant::class, 'tenantable');
    }
}
```

### Manage Your Tenants

Nothing special here, just normal [Eloquent](https://laravel.com/docs/5.4/eloquent) model stuff:

```php
use Rinvex\Tenantable\Tenant;

// Create a new tenant
Tenant::create([
    'name' => 'ACME Inc.',
    'slug' => 'acme',
    'owner_id' => '1',
    'email' => 'owner@acme.inc',
    'language_code' => 'en',
    'country_code' => 'us',
]);

// Get existing tenant by id
Tenant::find(1);
```

> **Notes:** since **Rinvex Tenantable** extends and utilizes other awesome packages, checkout the following documentations for further details:
> - Translatable out of the box using [`spatie/laravel-translatable`](https://github.com/spatie/laravel-translatable)
> - Automatic Slugging using [`spatie/laravel-sluggable`](https://github.com/spatie/laravel-sluggable)

### Activate Your Tenant

**Rinvex Tenantable** is stateless, which means you have to set the active tenant on every request, therefore it will only scope that specific request.

Make sure to activate your tenants in such a way that it happens on every request, and before you need Models scoped, like in a middleware or as part of a stateless authentication method like OAuth.

By default we set the active tenant by setting a runtime config value, [the normal way](https://laravel.com/docs/5.4/configuration#accessing-configuration-values):

```php
config(['rinvex.tenantable.tenant' => 1]);
```

You can pass either tenant id, slug, or instance. This package is smart enough to figure it out.

Note that you can only activate one tenant at a time, even if your resources belongs to multiple tenants, only one tenant could be active. You still have the ability to change the active tenant at any point of the request, but note that it will have scoping effect only on those models requested after that change, while any other models requested at an earlier stage of the request will be scoped with the previous tenant, or not scoped at all (according to your logic).

To deactivate your tenant and stop scoping by it, simply unset that runtime config value as follows:

```php
config(['rinvex.tenantable.tenant' => null]);
```

### Querying Tenant scoped Models
    
After you've added tenants, all queries against a Model which uses `Tenantable` will be scoped automatically:

```php
// This will only include Models belonging to the active tenant
\App\Product::all();

// This will fail with a `ModelNotFoundForTenantException` if it belongs to the wrong tenant (if active tenant is 1 for example)
\App\Product::find(2);
```

If you need to query across all tenants, you can use `forAllTenants` method:

```php
// Will include results from ALL tenants, just for this query
Product::forAllTenants()->get();
```

Under the hood, **Rinvex Tenantable** uses Laravel's [anonymous global scopes](https://laravel.com/docs/5.4/eloquent#global-scopes), which means if you are scoping by active tenant, and you want to exclude one single query, you can do so:

```php
// Will NOT be scoped, and will return results from ALL tenants, just for this query
Product::withoutGlobalScope('tenant')->get();
```

> **Notes:**
> - When you are developing multi-tenancy applications, it can be confusing sometimes why you keep getting `ModelNotFound` exceptions for rows that **DO** exist, because they belong to the wrong tenant.
> - **Rinvex Tenantable** will catch those exceptions, and re-throw them as `ModelNotFoundForTenantException`, to help you out 🙂

### Manage Your Tenantable Model

The API is intutive and very straightfarwad, so let's give it a quick look:

```php
// Instantiate your model
$product = \App\Product::find(1);

// Attach given tenants to the model
// accepts tenant id, instance, array of ids,
// or collection of instances, same signature as $model->sync()
$product->attachTenants([1, 2]);

// Alternatively you can pass tenants as an attribute to the model
$product->fill([
    'tenants' => [1, 2],
])->save();

// Detach given tenants from the model
// accepts tenant id, instance, array of ids,
// or collection of instances, same signature as $model->sync()
$product->detachTenants(2);

// Remove all attached tenants
$product->detachTenants();

// Get attached tenants collection
$product->tenants;

// Get attached tenants array with ids and names
$product->tenantList();
```

### Advanced Usage

#### Retrieve All Models Attached To The Tenant

It's very easy to get all models attached to certain tenant as follows:

```php
$tenant = Tenant::find(1);
$tenant->entries(\App\Product::class);
```

#### Query Scopes

Yes, **Rinvex Tenantable** shipped with few awesome query scopes for your convenience, usage example:

```php
// Get models with all given tenants
Product::withAllTenants([1, 2])->get();

// Get models with any given tenants
Product::withAnyTenants([1, 2])->get();

// Get models without tenants
Product::withoutTenants([1, 2])->get();

// Get models without any tenants
Product::withoutAnyTenants()->get();
```

As you may have expected, all of the scopes accepts tenant id, slug, instance, array of ids, or even collection of instances. Check source code for deeper insights 😉

#### Tenant Translations

Manage tenant translations with ease as follows:

```php
$tenant = Tenant::find(1);

// Set tenant translation
$tenant->setTranslation('name', 'en', 'Name in English');

// Get tenant translation
$tenant->setTranslation('name', 'en');

// Get tenant name in default locale
$tenant->name;
```


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

(c) 2016-2017 Rinvex LLC, Some rights reserved.
