<?php

declare(strict_types=1);

namespace Rinvex\Tenantable\Contracts;

/**
 * Rinvex\Tenantable\Contracts\TenantContract.
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
interface TenantContract
{
    //
}
