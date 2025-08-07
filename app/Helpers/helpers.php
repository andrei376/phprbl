<?php
/**
 * Returns the translations array.
 * These locales will be sent to Vue via the Inertia's share method.
 * @param string $json - The locale whose translations you want to find
 * @return array
 */

use GeoIp2\Database\Reader;

function translations(string $json): array
{
    if(!file_exists($json)) {
        return [];
    }

    return json_decode(file_get_contents($json), true);
}

function geoip_country_name_by_name($ip)
{
    //The geoip_country_name_by_name() function will return the full country name corresponding to a hostname or an IP address.
    $filename = '/var/lib/GeoIP/GeoIP2-City.mmdb';

    if (file_exists($filename)) {
        $cityDbReader = new Reader($filename);

        $record = $cityDbReader->city($ip);

        return $record->country->name;
    }

    return 'TODO GEOIP geoip_country_name_by_name '.__FILE__;
}
function geoip_country_code_by_name($ip)
{
    //The geoip_country_code_by_name() function will return the two letter country code corresponding to a hostname or an IP address.
    $filename = '/var/lib/GeoIP/GeoIP2-City.mmdb';

    if (file_exists($filename)) {
        $cityDbReader = new Reader($filename);

        $record = $cityDbReader->city($ip);

        return $record->country->isoCode;
    }

    return 'TODO GEOIP geoip_country_code_by_name '.__FILE__;
}
?>
