# Rinvex Tenants Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v6.0.5] - 2021-05-24
- Merge rules instead of resetting, to allow adequate model override
- Update spatie/laravel-translatable composer package to v5.0.0
- Update spatie/laravel-sluggable composer package to v3.0.0

## [v6.0.4] - 2021-05-11
- Fix constructor initialization order (fill attributes should come next after merging fillables & rules)

## [v6.0.3] - 2021-05-07
- Drop old MySQL versions support that doesn't support json columns
- Upgrade to GitHub-native Dependabot
- Use app() method alias `has` instead of `bound` for better readability
- Utilize SoftDeletes

## [v6.0.2] - 2021-02-06
- Simplify service provider model registration into IoC
- Enable StyleCI risky mode

## [v6.0.1] - 2020-12-25
- Add support for PHP v8

## [v6.0.0] - 2020-12-22
- Upgrade to Laravel v8
- Move custom eloquent model events to module layer from core package layer
- Refactor and tweak Eloquent Events

## [v5.0.2] - 2020-08-04
- Make sure `request.tenant` IoC service already bound before using
- Update timezone validation rule

## [v5.0.1] - 2020-07-16
- Update timezone validation rule
- Update validation rules

## [v5.0.0] - 2020-06-19
- Refactor active tenant container service binding

## [v4.1.0] - 2020-06-15
- Fix attaching categories by their IDs where IDs are passed mistakenly as strings in some cases!
- Fix phone validation rule
- Drop using rinvex/laravel-cacheable from core packages for more flexibility
  - Caching should be handled on the application layer, not enforced from the core packages
- Drop PHP 7.2 & 7.3 support from travis

## [v4.0.6] - 2020-05-30
- Remove default indent size config
- Add strip_tags validation rule to string fields
- Specify events queue
- Explicitly specify relationship attributes
- Add strip_tags validation rule
- Explicitly define relationship name

## [v4.0.5] - 2020-04-12
- Fix ServiceProvider registerCommands method compatibility

## [v4.0.4] - 2020-04-09
- Tweak artisan command registration
- Reverse commit "Convert database int fields into bigInteger"
- Refactor publish command and allow multiple resource values

## [v4.0.3] - 2020-04-04
- Fix namespace issue

## [v4.0.2] - 2020-04-04
- Enforce consistent artisan command tag namespacing
- Enforce consistent package namespace
- Drop laravel/helpers usage as it's no longer used

## [v4.0.1] - 2020-03-20
- Convert into bigInteger database fields
- Add shortcut -f (force) for artisan publish commands
- Fix migrations path

## [v4.0.0] - 2020-03-15
- Upgrade to Laravel v7.1.x & PHP v7.4.x

## [v3.0.3] - 2020-03-13
- Tweak TravisCI config
- Add migrations autoload option to the package
- Tweak service provider `publishesResources`
- Remove indirect composer dependency
- Drop using global helpers
- Update StyleCI config

## [v3.0.2] - 2019-12-18
- Fix `migrate:reset` args as it doesn't accept --step
- Create event classes and map them in the model

## [v3.0.1] - 2019-09-24
- Add missing laravel/helpers composer package

## [v3.0.0] - 2019-09-23
- Upgrade to Laravel v6 and update dependencies

## [v2.1.1] - 2019-06-03
- Enforce latest composer package versions

## [v2.1.0] - 2019-06-02
- Update composer deps
- Drop PHP 7.1 travis test
- Refactor migrations and artisan commands, and tweak service provider publishes functionality

## [v2.0.0] - 2019-03-03
- Rename environment variable QUEUE_DRIVER to QUEUE_CONNECTION
- Require PHP 7.2 & Laravel 5.8
- Apply PHPUnit 8 updates
- Replace get_called_class() with static::class (potentially deprecated in PHP 7.4)
- Refactor isManager & isSupermanager methods
- Drop ownership feature of tenants

## [v1.0.3] - 2018-12-23
- Add missing countries & languages dependencies (fix #19)

## [v1.0.2] - 2018-12-22
- Update composer dependencies
- Add PHP 7.3 support to travis
- Fix MySQL / PostgreSQL json column compatibility

## [v1.0.1] - 2018-10-05
- Fix wrong composer package version constraints

## [v1.0.0] - 2018-10-01
- Enforce Consistency
- Support Laravel 5.7+
- Rename package to rinvex/laravel-tenants

## [v0.0.5] - 2018-09-21
- Update travis php versions
- Define polymorphic relationship parameters explicitly
- Rename tenant "user" to "owner"
- Add isOwner and isStaff model methods
- Install composer package propaganistas/laravel-phone for phone verification
- Require composer package rinvex/languages
- Loose strongly typed return value of owner relationship for flexible override on module level
- Remove group and add timezone, currency attributes
- Drop StyleCI multi-language support (paid feature now!)
- Update composer dependencies
- Prepare and tweak testing configuration
- Highlight variables in strings explicitly
- Update StyleCI options
- Update PHPUnit options
- Rename model activation and deactivation methods
- Add tag model factory

## [v0.0.4] - 2018-02-18
- Rename tenantable global scope
- Update supplementary files
- Add PublishCommand to artisan
- Move slug auto generation to the custom HasSlug trait
- Add Rollback Console Command
- Refactor tenants active instance
- Remove useless TenantBadFormatException exception
- Add missing composer dependencies
- Typehint method returns
- Update composer dependencies
- Drop useless model contracts (models already swappable through IoC)
- Add Laravel v5.6 support
- Simplify IoC binding
- Convert tenant owner to polymorphic and rename it to user
- Drop Laravel 5.5 support
- Update PHPUnit to 7.0.0

## [v0.0.3] - 2017-09-09
- Fix many issues and apply many enhancements
- Rename package rinvex/laravel-tenants from rinvex/tenantable

## [v0.0.2] - 2017-06-29
- Enforce consistency
- Tweak active flag column
- Add Laravel 5.5 support
- Replace hardcoded table names
- Tweak model event registration
- Drop sorting order support, no need
- Add owner_id foreign key constraint
- Fix wrong slug generation method order
- Fix country_code & language_code validation rules
- Fix validation rules and check owner id existence

## v0.0.1 - 2017-04-11
- Tag first release

[v6.0.5]: https://github.com/rinvex/laravel-tenants/compare/v6.0.4...v6.0.5
[v6.0.4]: https://github.com/rinvex/laravel-tenants/compare/v6.0.3...v6.0.4
[v6.0.3]: https://github.com/rinvex/laravel-tenants/compare/v6.0.2...v6.0.3
[v6.0.2]: https://github.com/rinvex/laravel-tenants/compare/v6.0.1...v6.0.2
[v6.0.1]: https://github.com/rinvex/laravel-tenants/compare/v6.0.0...v6.0.1
[v6.0.0]: https://github.com/rinvex/laravel-tenants/compare/v5.0.2...v6.0.0
[v5.0.2]: https://github.com/rinvex/laravel-tenants/compare/v5.0.1...v5.0.2
[v5.0.1]: https://github.com/rinvex/laravel-tenants/compare/v5.0.0...v5.0.1
[v5.0.0]: https://github.com/rinvex/laravel-tenants/compare/v4.1.0...v5.0.0
[v4.1.0]: https://github.com/rinvex/laravel-tenants/compare/v4.0.6...v4.1.0
[v4.0.6]: https://github.com/rinvex/laravel-tenants/compare/v4.0.5...v4.0.6
[v4.0.5]: https://github.com/rinvex/laravel-tenants/compare/v4.0.4...v4.0.5
[v4.0.4]: https://github.com/rinvex/laravel-tenants/compare/v4.0.3...v4.0.4
[v4.0.3]: https://github.com/rinvex/laravel-tenants/compare/v4.0.2...v4.0.3
[v4.0.2]: https://github.com/rinvex/laravel-tenants/compare/v4.0.1...v4.0.2
[v4.0.1]: https://github.com/rinvex/laravel-tenants/compare/v4.0.0...v4.0.1
[v4.0.0]: https://github.com/rinvex/laravel-tenants/compare/v3.0.3...v4.0.0
[v3.0.3]: https://github.com/rinvex/laravel-tenants/compare/v3.0.2...v3.0.3
[v3.0.2]: https://github.com/rinvex/laravel-tenants/compare/v3.0.1...v3.0.2
[v3.0.1]: https://github.com/rinvex/laravel-tenants/compare/v3.0.0...v3.0.1
[v3.0.0]: https://github.com/rinvex/laravel-tenants/compare/v2.1.1...v3.0.0
[v2.1.1]: https://github.com/rinvex/laravel-tenants/compare/v2.1.0...v2.1.1
[v2.1.0]: https://github.com/rinvex/laravel-tenants/compare/v2.0.0...v2.1.0
[v2.0.0]: https://github.com/rinvex/laravel-tenants/compare/v1.0.3...v2.0.0
[v1.0.3]: https://github.com/rinvex/laravel-tenants/compare/v1.0.2...v1.0.3
[v1.0.2]: https://github.com/rinvex/laravel-tenants/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/rinvex/laravel-tenants/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/rinvex/laravel-tenants/compare/v0.0.5...v1.0.0
[v0.0.5]: https://github.com/rinvex/laravel-tenants/compare/v0.0.4...v0.0.5
[v0.0.4]: https://github.com/rinvex/laravel-tenants/compare/v0.0.3...v0.0.4
[v0.0.3]: https://github.com/rinvex/laravel-tenants/compare/v0.0.2...v0.0.3
[v0.0.2]: https://github.com/rinvex/laravel-tenants/compare/v0.0.1...v0.0.2
