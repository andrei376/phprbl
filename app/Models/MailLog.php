<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use PDPhilip\Elasticsearch\Eloquent\Model;

/**
 * @mixin IdeHelperSetup
 */
class MailLog extends Model
{
    use HasFactory;

    protected $connection = 'elasticsearch';

    protected $index  = '.ds-filebeat-*';

    protected $dates = [
        '@timestamp',
    ];

    protected $appends = [
        'time_format',
        'time_ago'
    ];

    public function getTimeFormatAttribute()
    {
        $field = "@timestamp";
        if (is_null($this->$field)) {
            return null;
        }
        return $this->$field->format('d F Y, H:i:s');
    }

    public function getTimeAgoAttribute()
    {
        $field = "@timestamp";
        if (is_null($this->$field)) {
            return null;
        }
        return $this->$field->diffForHumans();
    }
}
