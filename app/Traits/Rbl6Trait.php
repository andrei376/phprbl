<?php

namespace App\Traits;

use App\Helpers\Rbl6;
use App\Models\Hit;

trait Rbl6Trait
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
        return inet_ntop($this->getRawOriginal('iplong'));
    }

    public function getIplongAttribute($value)
    {
        //dd($this);
        return bin2hex($value);
    }

    public function getRangeAttribute()
    {
        $c6 = new Rbl6();
        return $c6->getRange(inet_ntop($this->getRawOriginal('iplong')).'/'.$this->mask, 'string');
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
}
