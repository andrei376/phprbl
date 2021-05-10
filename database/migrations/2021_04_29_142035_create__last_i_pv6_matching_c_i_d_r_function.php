<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateLastIPv6MatchingCIDRFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = 'DROP FUNCTION IF EXISTS LastIPv6MatchingCIDR;
        CREATE FUNCTION `LastIPv6MatchingCIDR` (`ip` VARCHAR(46), `mask` INT(2) UNSIGNED) RETURNS VARCHAR(39) DETERMINISTIC
    BEGIN
        DECLARE `ipNumber` VARBINARY(16);
        DECLARE `last` VARCHAR(39) DEFAULT \'\';
        DECLARE `flexBits`, `counter`, `deci`, `newByte` INT UNSIGNED;
        DECLARE `hexIP` VARCHAR(32);

        SET `ipNumber` = INET6_ATON(`ip`);
        SET `hexIP`    = HEX(`ipNumber`);
        SET `flexBits` = 128 - `mask`;
        SET `counter`  = 32;

        WHILE (`flexBits` > 0) DO
            SET `deci`    = CONV(SUBSTR(`hexIP`, `counter`, 1), 16, 10);
            SET `newByte` = `deci` | (POW(2, LEAST(4, `flexBits`)) - 1);
            SET `last`    = CONCAT(CONV(`newByte`, 10, 16), `last`);

            IF `flexBits` >= 4 THEN
                SET `flexBits` = `flexBits` - 4;
            ELSE
                SET `flexBits` = 0;
            END IF;

            SET `counter`  = `counter` - 1;
        END WHILE;

        SET `last` = CONCAT(SUBSTR(`hexIP`, 1, `counter`), `last`);

        RETURN INET6_NTOA(UNHEX(`last`));
    END;
        ';

        try {
            $result = DB::unprepared($sql);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage()."\n".
                ", trace: ".$e->getTraceAsString()."\n\n"
            );
        }

        /*Log::debug(
            __METHOD__.
            " result=".print_r($result, true)."\n\n"
        );*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS LastIPv6MatchingCIDR;');
    }
}
