<?php

namespace App\Models;

use App\Traits\CountIpTrait;
use App\Traits\IpInListTrait;
use App\Traits\Rbl6Trait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBlack6
 */
class Black6 extends Model
{
    use HasFactory, IpInListTrait, CountIpTrait, Rbl6Trait;

    protected $fillable = [
        'ip1',
        'ip2',
        'ip3',
        'ip4',
        'ip5',
        'ip6',
        'ip7',
        'ip8',

        'iplong',
        'mask',

        'inetnum',
        'netname',
        'country',
        'orgname',
        'geoipcountry',

        'delete',
        'active',

        'last_check',

        'checked'
    ];

    protected $casts = [
        'date_added' => 'datetime',
        'last_check' => 'datetime',
        'delete' => 'boolean',
        'active' => 'boolean',
        'checked' => 'boolean',
    ];

    protected $appends = [
        'date_added_format',
        'date_added_ago',
        'last_check_format',
        'last_check_ago',
        'long2ip',
        'range',
        'total_ip_format',
        'hits_sum_count_format',
        'row_count_format'
    ];
}
