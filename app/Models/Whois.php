<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperWhois
 */
class Whois extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',

        'iplong',
        'mask',

        'range',

        'inetnum',
        'netname',
        'country',
        'orgname',

        'output'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public static function isInDb($cidr)
    {
        list($ip, $prefix) = explode('/', $cidr);

        //
        $bc = (ip2long($ip)+(pow(2,32-$prefix)-1));

        //dump(__METHOD__.' '.__LINE__.' ');
        //dump("testing $ip/$prefix = $ip->".long2ip($bc));

        if ($ret = self::where([['iplong', ip2long($ip)], ['mask', $prefix]])->first()) {
            return $ret;
        } elseif($ret = self::where([['iplong', '<=', ip2long($ip)]])->whereRaw('(`iplong`+(pow(2,32-`mask`)-1)) >= ?', [$bc])->first()) {
            //dump('in range');

            //dump($ret);
            return $ret;
        } else {
            return false;
        }
    }
}
