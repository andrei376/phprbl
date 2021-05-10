<?php

namespace App\Traits;

use App\Helpers\Rbl4;
use App\Models\Hit;
use Illuminate\Support\Facades\DB;

trait RblTrait
{
    public function hits()
    {
        return $this->morphMany(Hit::class, 'hits', 'list', 'list_id');
    }

    public function getDateAddedFormatAttribute()
    {
        if (is_null($this->date_added)) {
            return null;
        }
        return $this->date_added->format('d F Y, H:i:s');
    }

    public function getLastCheckFormatAttribute()
    {
        if (is_null($this->last_check)) {
            return 'never';
        }

        return $this->last_check->format('d F Y, H:i:s');
    }

    public function getDateAddedAgoAttribute()
    {
        if (is_null($this->date_added)) {
            return null;
        }
        return $this->date_added->diffForHumans();
    }

    public function getLastCheckAgoAttribute()
    {
        if (is_null($this->last_check)) {
            return null;
        }

        return $this->last_check->diffForHumans();
    }

    public function getLong2IpAttribute()
    {
        return long2ip($this->iplong);
    }

    public function getRangeAttribute()
    {
        $c4 = new Rbl4();
        return $c4->getRange(long2ip($this->iplong).'/'.$this->mask,'string');
    }

    public function getTotalIpFormatAttribute(): ?string
    {
        if (is_null($this->total_ip)) {
            return null;
        }

        return number_format ($this->total_ip ,  0 ,  "," ,  "." );
    }

    public function getHitsSumCountFormatAttribute(): ?string
    {
        if (is_null($this->hits_sum_count)) {
            return null;
        }

        return number_format ($this->hits_sum_count ,  0 ,  "," ,  "." );
    }

    public function getRowCountFormatAttribute(): ?string
    {
        if (is_null($this->row_count)) {
            return null;
        }

        return number_format ($this->row_count ,  0 ,  "," ,  "." );
    }

    public function ips24()
    {
        /*$sql = "select $lista.id,$lista.ip1,$lista.ip2,$lista.ip3,$lista.ip4,$lista.inetnum from " .
            strtolower($lista)." as `$lista`" .
            " inner join " .
            "(select ip1,ip2,ip3 from ".strtolower($lista)." where `active`='1' and checked='1' group by ip1,ip2,ip3 having" .
            " count(ip4) > ".$s['ips'].") as x" .
            " on x.ip1=$lista.ip1 and x.ip2=$lista.ip2 and x.ip3=$lista.ip3 where " .
            "active='1' and $lista.checked='1' and last_check <= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH) order by $lista.ip1,$lista.ip2,$lista.ip3,$lista.ip4";*/

        //pr($sql);
        // $rez = $this->{$lista}->query($sql);

        $search24 = 2;

        $table = $this->getTable();

        $subquery = self::
            select(['ip1', 'ip2', 'ip3'])
            ->where('active', 1)
            ->where('checked', 1)
            ->groupBy(['ip1', 'ip2', 'ip3'])
            ->havingRaw('count(ip4) > ?', [$search24]);

            // ->toSql();

        //dump($subquery->toSql());


        $rows = $this->
            select([$table.'.id', $table.'.ip1', $table.'.ip2', $table.'.ip3', $table.'.ip4', $table.'.inetnum'])
            ->joinSub($subquery, 'x', function ($join) {
                $table = $this->getTable();

                $join->on('x.ip1', '=', $table.'.ip1')
                    ->on('x.ip2', '=', $table.'.ip2')
                    ->on('x.ip3', '=', $table.'.ip3');
            })
            ->where('active', 1)
            ->where('checked', 1)
            ->where('last_check', '<=', DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)'))
            ->orderBy('ip1')
            ->orderBy('ip2')
            ->orderBy('ip3')
            ->orderBy('ip4')
            ->get();

        return $rows;
    }

    public function classes16()
    {
        /*$sql = "select $lista.id,$lista.ip1,$lista.ip2,$lista.ip3,$lista.ip4,$lista.inetnum from " .
            strtolower($lista)." as `$lista` inner join " .
            "(select ip1,ip2 from ".strtolower($lista)." where `active`='1' and ip4='0' and checked='1' group by ip1,ip2 having count(ip3)>".$s['clase'].") as x" .
            " on x.ip1=$lista.ip1 and x.ip2=$lista.ip2 where " .
            "$lista.active=1 and $lista.checked='1' and last_check <= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH) order by $lista.ip1,$lista.ip2,$lista.ip3,$lista.ip4";*/

        $search16 = 9;

        $table = $this->getTable();

        $subquery = self::
        select(['ip1', 'ip2'])
            ->where('active', 1)
            ->where('checked', 1)
            ->where('ip4', 0)
            ->groupBy(['ip1', 'ip2'])
            ->havingRaw('count(ip3) > ?', [$search16]);

        // ->toSql();

        //dump($subquery->toSql());


        $rows = $this->
        select([$table.'.id', $table.'.ip1', $table.'.ip2', $table.'.ip3', $table.'.ip4', $table.'.inetnum'])
            ->joinSub($subquery, 'x', function ($join) {
                $table = $this->getTable();

                $join->on('x.ip1', '=', $table.'.ip1')
                    ->on('x.ip2', '=', $table.'.ip2');
            })
            ->where('active', 1)
            ->where('checked', 1)
            ->where('last_check', '<=', DB::raw('DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)'))
            ->orderBy('ip1')
            ->orderBy('ip2')
            ->orderBy('ip3')
            ->orderBy('ip4')
            ->get();

        return $rows;
    }
}
