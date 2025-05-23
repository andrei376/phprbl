<?php
/**
 * Returns the translations array.
 * These locales will be sent to Vue via the Inertia's share method.
 * @param string $json - The locale whose translations you want to find
 * @return array
 */
function translations(string $json): array
{
    if(!file_exists($json)) {
        return [];
    }

    return json_decode(file_get_contents($json), true);
}

function geoip_country_name_by_name($ip)
{
    return 'TODO GEOIP '.__FILE__;
}
function geoip_country_code_by_name($ip)
{
    return 'TODO GEOIP '.__FILE__;
}
?>
