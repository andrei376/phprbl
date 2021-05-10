<?php

namespace App\Traits;


use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait CountIpTrait
{
    public static function countIp($v6 = false): string
    {
        $bits = 32;

        if ($v6) {
            $bits = 128;
        }

        $formatTotal = 0;
        try {
           // DB::enableQueryLog();
            $data = self::
            where('active', 1)
                ->where('delete', 0)
                ->select(DB::raw('SUM(POW(2,'.$bits.'-`mask`)) as total'))
                ->first();

           // dump(DB::getQueryLog());

            $formatTotal = number_format ($data->total ,  0 ,  "," ,  "." );
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

        //dump($data);


        return $formatTotal;
    }

    public static function checkCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
                where('checked', 0)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }

    public static function netnameCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
                where('netname', null)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }

    public static function deleteCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
                where('delete', 1)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }

    public static function inactiveCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
            where('active', 0)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }
}
