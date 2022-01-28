<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Models;

use Spatie\Sluggable\SlugOptions;
use Rinvex\Support\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Support\Traits\HasTranslations;
use Rinvex\Support\Traits\ValidatingTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Rinvex\Tenants\Models\Tenant.
 *
 * @property int                 $id
 * @property string              $slug
 * @property string              $domain
 * @property array               $title
 * @property array               $description
 * @property string              $email
 * @property string              $website
 * @property string              $phone
 * @property string              $language_code
 * @property string              $country_code
 * @property string              $state
 * @property string              $city
 * @property string              $address
 * @property string              $postal_code
 * @property string              $launch_date
 * @property string              $timezone
 * @property string              $currency
 * @property string              $group
 * @property bool                $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Rinvex\Country\Country                       $country
 * @property-read \Rinvex\Language\Language                     $language
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereLaunchDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Rinvex\Tenants\Models\Tenant whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tenant extends Model
{
    use HasSlug;
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;
    use ValidatingTrait;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'slug',
        'domain',
        'name',
        'description',
        'email',
        'website',
        'phone',
        'language_code',
        'country_code',
        'state',
        'city',
        'address',
        'postal_code',
        'launch_date',
        'timezone',
        'currency',
        'is_active',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug' => 'string',
        'domain' => 'string',
        'email' => 'string',
        'website' => 'string',
        'phone' => 'string',
        'country_code' => 'string',
        'language_code' => 'string',
        'state' => 'string',
        'city' => 'string',
        'address' => 'string',
        'postal_code' => 'string',
        'launch_date' => 'string',
        'timezone' => 'string',
        'currency' => 'string',
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
        $this->setTable(config('rinvex.tenants.tables.tenants'));
        $this->mergeRules([
            'slug' => 'required|alpha_dash|max:150|unique:'.config('rinvex.tenants.tables.tenants').',slug',
            'domain' => 'nullable|strip_tags|max:150|unique:'.config('rinvex.tenants.tables.tenants').',domain',
            'name' => 'required|string|strip_tags|max:150',
            'description' => 'nullable|string|max:32768',
            'email' => 'required|email|min:3|max:128|unique:'.config('rinvex.tenants.tables.tenants').',email',
            'website' => 'nullable|url|max:1500',
            'phone' => 'required|phone:AUTO',
            'country_code' => 'required|alpha|size:2|country',
            'language_code' => 'required|alpha|size:2|language',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'launch_date' => 'nullable|date_format:Y-m-d',
            'timezone' => 'nullable|string|max:64|timezone',
            'currency' => 'required|alpha|size:3',
            'is_active' => 'sometimes|boolean',
        ]);

        parent::__construct($attributes);
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
        return $this->morphedByMany($class, 'tenantable', config('rinvex.tenants.tables.tenantables'), 'tenant_id', 'tenantable_id', 'id', 'id');
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
     * Determine if the given model is supermanager of the tenant.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function isSuperManager(Model $model): bool
    {
        return $this->isManager($model) && $model->isA('supermanager');
    }

    /**
     * Determine if the given model is manager of the tenant.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function isManager(Model $model): bool
    {
        return $model->tenants->contains($this);
    }

    /**
     * Activate the tenant.
     *
     * @return $this
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate the tenant.
     *
     * @return $this
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
