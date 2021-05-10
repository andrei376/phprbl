<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Black
 *
 * @mixin IdeHelperBlack
 * @property int $id
 * @property int $ip1
 * @property int $ip2
 * @property int $ip3
 * @property int $ip4
 * @property int $iplong
 * @property int $mask
 * @property string|null $inetnum
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $geoipcountry
 * @property bool $delete
 * @property bool $active
 * @property \Illuminate\Support\Carbon $date_added
 * @property \Illuminate\Support\Carbon|null $last_check
 * @property bool $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $date_added_ago
 * @property-read mixed $date_added_format
 * @property-read string|null $hits_sum_count_format
 * @property-read mixed $last_check_ago
 * @property-read mixed $last_check_format
 * @property-read mixed $long2_ip
 * @property-read mixed $range
 * @property-read string|null $row_count_format
 * @property-read string|null $total_ip_format
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Hit[] $hits
 * @property-read int|null $hits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Black newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Black newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Black query()
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereDateAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereGeoipcountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereIp1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereIp2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereIp3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereIp4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereIplong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereLastCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Black whereUpdatedAt($value)
 */
	class IdeHelperBlack extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Black6
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Black6 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Black6 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Black6 query()
 * @mixin \Eloquent
 */
	class IdeHelperBlack6 extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DefineList
 *
 * @mixin IdeHelperDefineList
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $expire
 * @property string $host
 * @property string $list
 * @property string $minttl
 * @property string $nss
 * @property string $primaryns
 * @property string $refresh
 * @property string $retry
 * @property string $soansttl
 * @property int $currentsn
 * @property int $lastsn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList query()
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereCurrentsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereExpire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereLastsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereList($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereMinttl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereNss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList wherePrimaryns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereRefresh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereRetry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereSoansttl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DefineList whereUpdatedAt($value)
 */
	class IdeHelperDefineList extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Grey
 *
 * @mixin IdeHelperGrey
 * @property int $id
 * @property int $ip1
 * @property int $ip2
 * @property int $ip3
 * @property int $ip4
 * @property int $iplong
 * @property int $mask
 * @property string|null $inetnum
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $geoipcountry
 * @property bool $delete
 * @property bool $active
 * @property \Illuminate\Support\Carbon $date_added
 * @property \Illuminate\Support\Carbon|null $last_check
 * @property bool $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $date_added_ago
 * @property-read mixed $date_added_format
 * @property-read string|null $hits_sum_count_format
 * @property-read mixed $last_check_ago
 * @property-read mixed $last_check_format
 * @property-read mixed $long2_ip
 * @property-read mixed $range
 * @property-read string|null $row_count_format
 * @property-read string|null $total_ip_format
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Hit[] $hits
 * @property-read int|null $hits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Grey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grey query()
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereDateAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereGeoipcountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereIp1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereIp2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereIp3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereIp4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereIplong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereLastCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Grey whereUpdatedAt($value)
 */
	class IdeHelperGrey extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Grey6
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Grey6 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grey6 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Grey6 query()
 * @mixin \Eloquent
 */
	class IdeHelperGrey6 extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Hit
 *
 * @mixin IdeHelperHit
 * @property int $id
 * @property string $list
 * @property int $list_id
 * @property int $year
 * @property int $month
 * @property int $day
 * @property int $count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Hit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereList($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hit whereYear($value)
 */
	class IdeHelperHit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RblLog
 *
 * @mixin IdeHelperRblLog
 * @property int $id
 * @property string $user
 * @property \Illuminate\Support\Carbon $date
 * @property string $type
 * @property bool $read
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $date_ago
 * @property-read mixed $date_format
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RblLog whereUser($value)
 */
	class IdeHelperRblLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Setup
 *
 * @mixin IdeHelperSetup
 * @property int $id
 * @property string $name
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Setup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setup query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setup whereValue($value)
 */
	class IdeHelperSetup extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class IdeHelperUser extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * App\Models\White
 *
 * @mixin IdeHelperWhite
 * @property int $id
 * @property int $ip1
 * @property int $ip2
 * @property int $ip3
 * @property int $ip4
 * @property int $iplong
 * @property int $mask
 * @property string|null $inetnum
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $geoipcountry
 * @property bool $delete
 * @property bool $active
 * @property \Illuminate\Support\Carbon $date_added
 * @property \Illuminate\Support\Carbon|null $last_check
 * @property bool $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $date_added_ago
 * @property-read mixed $date_added_format
 * @property-read string|null $hits_sum_count_format
 * @property-read mixed $last_check_ago
 * @property-read mixed $last_check_format
 * @property-read mixed $long2_ip
 * @property-read mixed $range
 * @property-read string|null $row_count_format
 * @property-read string|null $total_ip_format
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Hit[] $hits
 * @property-read int|null $hits_count
 * @method static \Illuminate\Database\Eloquent\Builder|White newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|White newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|White query()
 * @method static \Illuminate\Database\Eloquent\Builder|White whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereDateAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereGeoipcountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereIp1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereIp2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereIp3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereIp4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereIplong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereLastCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White whereUpdatedAt($value)
 */
	class IdeHelperWhite extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\White6
 *
 * @mixin IdeHelperWhite6
 * @property int $id
 * @property string $ip1
 * @property string $ip2
 * @property string $ip3
 * @property string $ip4
 * @property string $ip5
 * @property string $ip6
 * @property string $ip7
 * @property string $ip8
 * @property mixed $iplong
 * @property int $mask
 * @property string|null $inetnum
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $geoipcountry
 * @property bool $delete
 * @property bool $active
 * @property \Illuminate\Support\Carbon $date_added
 * @property \Illuminate\Support\Carbon|null $last_check
 * @property bool $checked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|White6 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|White6 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|White6 query()
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereDateAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereGeoipcountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp6($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp7($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIp8($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereIplong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereLastCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|White6 whereUpdatedAt($value)
 */
	class IdeHelperWhite6 extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Whois
 *
 * @mixin IdeHelperWhois
 * @property int $id
 * @property \Illuminate\Support\Carbon $date
 * @property int $iplong
 * @property int $mask
 * @property string|null $inetnum
 * @property string|null $range
 * @property string|null $netname
 * @property string|null $country
 * @property string|null $orgname
 * @property string|null $output
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Whois newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Whois newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Whois query()
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereInetnum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereIplong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereMask($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereNetname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereOrgname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Whois whereUpdatedAt($value)
 */
	class IdeHelperWhois extends \Eloquent {}
}

