<?php

namespace App\Traits;

use App\Helpers\Rbl6;

trait IpInListTrait
{
    public static function isInDb6($ip, $prefix)
    {
        // dump($ip);
        // dump($prefix);

        $c6 = new Rbl6();

        $high = inet_pton($c6->getRange($ip.'/'.$prefix, 'high'));

        // dump(inet_ntop($high));

        if ($ret = self::where('active', 1)->where([['iplong', inet_pton($ip)], ['mask', $prefix]])->first()) {
            // dump('same1');

            if ($ret['active'] == 1 && $ret['delete'] == 0) {
                //the same
                return -1;
            } else {
                // update
                return $ret['id'];
            }
        } elseif ($ret = self::where('active', 1)->where([['iplong', '<=', inet_pton($ip)]])->whereRaw('INET6_ATON(LastIPv6MatchingCIDR(INET6_NTOA(`iplong`), `mask`)) >= ?', [$high])->first()) {
            // dump('in range');

            return -1;
        }

        return false;
    }

    public static function isInDb($ip, $prefix)
    {
        //
        $bc = (ip2long($ip)+(pow(2,32-$prefix)-1));

        //dump(__METHOD__.' '.__LINE__.' ');
        //dump("testing $ip/$prefix = $ip->".long2ip($bc));

        if ($ret = self::where([['iplong', ip2long($ip)], ['mask', $prefix]])->first()) {
            //dump('same');
            //
            //$ret = $ret['Black'];
            //dump($ret);
            //pr($ret);
            if ($ret['active'] == 1 && $ret['delete'] == 0) {
                //the same
                return -1;
            } else {
                // update
                return $ret['id'];
            }
        } elseif($ret = self::where([['iplong', '<=', ip2long($ip)]])->whereRaw('(`iplong`+(pow(2,32-`mask`)-1)) >= ?', [$bc])->first()) {
            //dump('in range');

            //dump($ret);

            //$ret = $ret['Black'];

            if ($ret['active'] == 1 && $ret['delete'] == 0) {
                //same
                return -1;
            } else {
                // update
                return -1;
                //return $ret['id'];
            }
        } else {
            return false;
        }
    }
}
