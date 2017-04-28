<?php

declare(strict_types=1);

namespace Rinvex\Tenantable;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Watson\Validating\ValidatingTrait;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Cacheable\CacheableEloquent;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Rinvex\Tenantable\Tenant
 *
 * @property int                            $id
 * @property string                         $slug
 * @property array                          $name
 * @property array                          $description
 * @property int                            $owner_id
 * @property string                         $email
 * @property bool                           $email_verified
 * @property string                         $phone
 * @property bool                           $phone_verified
 * @property string                         $language_code
 * @property string                         $country_code
 * @property string                         $state
 * @property string                         $city
 * @property string                         $address
 * @property string                         $postal_code
 * @property string                         $launch_date
 * @property bool                           $active
 * @property string                         $group
 * @property \Carbon\Carbon                 $created_at
 * @property \Carbon\Carbon                 $updated_at
 * @property string                         $deleted_at
 * @property-read \Rinvex\Country\Country   $country
 * @property-read \Rinvex\Language\Language $language
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereCity($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereCountryCode($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereEmailVerified($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereGroup($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereLanguageCode($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereLaunchDate($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant wherePhoneVerified($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant wherePostalCode($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereState($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Rinvex\Tenantable\Tenant withGroup($group = null)
 * @mixin \Eloquent
 */
class Tenant extends Model
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
        'active',
        'group',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = ['validating', 'validated'];

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
     * Whether the model should throw a ValidationException if it fails validation.
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

        $this->setTable(config('rinvex.tenantable.tables.tenants'));
        $this->setRules([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'slug' => 'required|alpha_dash|unique:'.config('rinvex.tenantable.tables.tenants').',slug',
            'owner_id' => 'required|integer',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'language_code' => 'required|string|size:2',
            'country_code' => 'required|string|size:2',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        if (isset(static::$dispatcher)) {
            // Early auto generate slugs before validation
            static::$dispatcher->listen('eloquent.validating: '.static::class, function ($model, $event) {
                if (! $model->slug) {
                    if ($model->exists) {
                        $model->generateSlugOnCreate();
                    } else {
                        $model->generateSlugOnUpdate();
                    }
                }
            });
        }
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
     * Set the translatable name attribute.
     *
     * @param string $value
     *
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = json_encode(! is_array($value) ? [app()->getLocale() => $value] : $value);
    }

    /**
     * Set the translatable description attribute.
     *
     * @param string $value
     *
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = ! empty($value) ? json_encode(! is_array($value) ? [app()->getLocale() => $value] : $value) : null;
    }

    /**
     * Enforce clean slugs.
     *
     * @param string $value
     *
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_slug($value);
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
     * Scope tenants by given group.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null                           $group
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithGroup(Builder $query, string $group = null): Builder
    {
        return $group ? $query->where('group', $group) : $query;
    }

    /**
     * Find tenant by name.
     *
     * @param string      $name
     * @param string|null $locale
     *
     * @return static|null
     */
    public static function findByName(string $name, string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return static::query()->where("name->{$locale}", $name)->first();
    }

    /**
     * Get the tenant's country.
     *
     * @return \Rinvex\Country\Country
     */
    public function getCountryAttribute()
    {
        return country($this->country_code);
    }

    /**
     * Get the tenant's language.
     *
     * @return \Rinvex\Language\Language
     */
    public function getLanguageAttribute()
    {
        return language($this->language_code);
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
}
