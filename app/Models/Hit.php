<?php /** @noinspection PhpClassNamingConventionInspection */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperHit
 */
class Hit extends Model
{
    use HasFactory;

    protected $fillable = [
        'list',
        'list_id',
        'year',
        'month',
        'day',
        'count'
    ];
}
