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

    protected $casts = [
        '@timestamp' => 'datetime',
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

    public function testingceva()
    {
        /*
        $this->client->delete([
            'index' => '.ds-filebeat-8.2.0-2022.05.21-000001',
            'id' => 'Rb64H4EBmYwAdU2yHTCI'
        ]);*/
    }
}
