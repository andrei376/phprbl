<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @mixin IdeHelperDefineList
 */
class DefineList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'expire',
        'host',
        'list',
        'minttl',
        'nss',
        'primaryns',
        'refresh',
        'retry',
        'soansttl'
    ];

    public static function makeSync($list): bool
    {
        try {
            self::where('name', $list)->update(['lastsn' => DB::raw('`currentsn`')]);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " list=$list, error: ".$e->getMessage().
                ", trace:\n".
                $e->getTraceAsString().
                "\n"
            );

            return false;
        }

        return true;
    }

    public static function isSync($list): bool
    {
        try {
            $data = self::
                where('name', $list)
                ->select(['currentsn', 'lastsn'])
                ->firstOrFail();
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " list=$list, error: ".$e->getMessage().
                "\n"
            );

            return false;
        }




        //dump($data);

        $currentSn = $data->currentsn;
        $lastSn = $data->lastsn;

        if ($currentSn == $lastSn) {
            return true;
        }

        return false;
    }

    public static function increaseSn(string $list): bool
    {
        try {
            //DB::enableQueryLog();
            self::where('name', 'like', $list)->increment('currentsn');
            //dump(DB::getQueryLog());

            return true;
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " list=$list, error: ".$e->getMessage().
                "\n"
            );
        }

        return false;
    }
}
