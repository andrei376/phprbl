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
}
