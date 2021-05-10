<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSetup
 */
class Setup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'value'
    ];
}
