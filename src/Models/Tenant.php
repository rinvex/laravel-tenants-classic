<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Illuminate\Database\Eloquent\Builder;
use Rinvex\Support\Traits\HasTranslations;
use Rinvex\Support\Traits\ValidatingTrait;
use Rinvex\Tenantable\Contracts\TenantContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Rinvex\Tenantable\Models\Tenant.
 *
 * @property int                                                $id
 * @property string                                             $slug
 * @property array                                              $name
 * @property array                                              $description
 * @property int                                                $owner_id
 * @property string                                             $email
 * @property string                                             $phone
 * @property string                                             $language_code
 * @property string                                             $country_code
 * @property string                                             $state
 * @property string                                             $city
 * @property string                                             $address
 * @property string                                             $postal_code
 * @property string                                             $launch_date
 * @property string                                             $group
 * @property bool                                               $is_active
 * @property \Carbon\Carbon                                     $created_at
 * @property \Carbon\Carbon                                     $updated_at
 * @property \Carbon\Carbon                                     $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $owner
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant inactive()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereLaunchDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenantable\Models\Tenant withGroup($group = null)
 * @mixin \Eloquent
 */
class Tenant extends Model implements TenantContract
{
    use HasSlug;
    use HasTranslations;
    use ValidatingTrait;
    use CacheableEloquent;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'owner_id',
        'email',
        'phone',
        'language_code',
        'country_code',
        'state',
        'city',
        'address',
        'postal_code',
        'launch_date',
        'group',
        'is_active',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug' => 'string',
        'owner_id' => 'integer',
        'email' => 'string',
        'phone' => 'string',
        'country_code' => 'string',
        'language_code' => 'string',
        'state' => 'string',
        'city' => 'string',
        'address' => 'string',
        'postal_code' => 'string',
        'launch_date' => 'string',
        'group' => 'string',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Get users model
        $userModel = config('auth.providers.'.config('auth.guards.'.config('auth.defaults.guard').'.provider').'.model');

        $this->setTable(config('rinvex.tenantable.tables.tenants'));
        $this->setRules([
            'slug' => 'required|alpha_dash|max:150|unique:'.config('rinvex.tenantable.tables.tenants').',slug',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:10000',
            'owner_id' => 'required|integer|exists:'.(new $userModel())->getTable().',id',
            'email' => 'required|email|min:3|max:150|unique:'.config('rinvex.tenantable.tables.tenants').',email',
            'phone' => 'nullable|numeric|min:4',
            'country_code' => 'required|alpha|size:2|country',
            'language_code' => 'required|alpha|size:2|language',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'launch_date' => 'nullable|date_format:Y-m-d',
            'group' => 'nullable|string|max:150',
            'is_active' => 'sometimes|boolean',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        // Auto generate slugs early before validation
        static::validating(function (self $tenant) {
            if ($tenant->exists && $tenant->getSlugOptions()->generateSlugsOnUpdate) {
                $tenant->generateSlugOnUpdate();
            } elseif (! $tenant->exists && $tenant->getSlugOptions()->generateSlugsOnCreate) {
                $tenant->generateSlugOnCreate();
            }
        });
    }

    /**
     * Get all attached models of the given class to the tenant.
     *
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function entries(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'tenantable', config('rinvex.tenantable.tables.tenantables'), 'tenant_id', 'tenantable_id');
    }

    /**
     * Get the options for generating the slug.
     *
     * @return \Spatie\Sluggable\SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
                          ->doNotGenerateSlugsOnUpdate()
                          ->generateSlugsFrom('name')
                          ->saveSlugsTo('slug');
    }

    /**
     * Get the active tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('is_active', true);
    }

    /**
     * Get the inactive tenants.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive(Builder $builder): Builder
    {
        return $builder->where('is_active', false);
    }

    /**
     * Scope tenants by given group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGroup(Builder $builder, string $group): Builder
    {
        return $builder->where('group', $group);
    }

    /**
     * A tenant always belongs to an owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $userModel = config('auth.providers.'.config('auth.guards.'.config('auth.defaults.guard').'.provider').'.model');

        return $this->belongsTo($userModel, 'owner_id', 'id');
    }

    /**
     * Active the tenant.
     *
     * @return static
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate the tenant.
     *
     * @return static
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
