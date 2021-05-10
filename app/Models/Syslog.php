<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @mixin IdeHelperSetup
 */
class Syslog extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $collection = 'systemevents';

    protected $dates = [
        'time',
        'time_rcvd'
    ];

    protected $appends = [
        'time_format',
        'time_ago'
    ];

    public function getTimeFormatAttribute()
    {
        if (is_null($this->time)) {
            return null;
        }
        return $this->time->format('d F Y, H:i:s');
    }

    public function getTimeAgoAttribute()
    {
        if (is_null($this->time)) {
            return null;
        }
        return $this->time->diffForHumans();
    }
}
