<?php

namespace App\Helpers;

use App\Models\Whois;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhoisLib
{
    public function searchCache($ip, $force = false): array
    {
        $cacheKey = $ip;

        //check cache
        if ($force == false && $value = Cache::get($cacheKey)) {
            //dump($value);
            $value['output'] = json_decode($value['output']);

            //dump($value['output']);

            $value['output']->source = 'FROM CACHE';

            return $value;
        } elseif ((stripos($ip, ':')===false) && $force == false && $dbInfo = Whois::isInDb($ip)) {
            $dbInfo = $dbInfo->toArray();

            $dbInfo['output'] = json_decode($dbInfo['output']);

            $dbInfo['output']->source = 'FROM DB';

            $saveData = $dbInfo;
            $saveData['output'] = json_encode($saveData['output']);

            $this->saveCache($cacheKey, $saveData);

            return $dbInfo;
        } else {
            //get fresh data
            $data = $this->search($ip);
        }

        //save to db
        try {
            //
            $db['id'] = '';
            $db['iplong'] = sprintf("%u", $data['iplong']);
            $db['mask'] = $data['mask'];

            $db['date'] = $data['date'];


            $db['inetnum'] = $data['inetnum'];
            $db['range'] = $data['range'];
            $db['netname'] = $data['netname'];
            $db['country'] = $data['country'];
            $db['orgname'] = $data['orgname'];
            $db['output'] = $data['output'];

            //DB::enableQueryLog();

            //Whois::insertOrIgnore($db);

            if (stripos($ip, ':')===false) {
                Whois::updateOrCreate(
                    [
                        'iplong' => $db['iplong'],
                        'mask' => $db['mask']
                    ],
                    $db
                );
            }

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error saving whois in db: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n data=".
                print_r($db, true).
                "\n"
            );
        }

        $this->saveCache($cacheKey, $data);

        if (isset($data['output'])) {
            $data['output'] = json_decode($data['output']);
        }
        return $data;
    }

    public function saveCache($cacheKey, $data): bool
    {
        //save to cache
        $c4 = new Rbl4();

        $size = $c4->arraySize($data);

        if ($size < 1000 * 1000) {
            Cache::put($cacheKey, $data, now()->addDay());
        } else {
            Log::debug(
                __METHOD__.
                " error saving to cache (size = $size)\n"
            );

            return false;
        }

        return true;
    }

    public function search($ip): array
    {
        $data = $this->searchArin($ip);

        return $data;
    }

    public function searchRipeAs($asn): array
    {
        // dump('GET AS IP '.$asn);

        $url = 'https://stat.ripe.net/data/announced-prefixes/data.json?resource=' . $asn;

        $http = Http::withOptions([
            'allow_redirects' => true,
            'verify' => false
        ]);

        $emptyResult = [];

        try {
            $response = $http->get($url);
        } catch (Exception $e) {
            // Log::error('get host rdap.arin.net=' . gethostbyname('rdap.arin.net'));
            // Log::error('get host google.com=' . gethostbyname('google.com'));
            // Log::error('get host rdap.afrinic.net=' . gethostbyname('rdap.afrinic.net'));

            Log::error(__("[EXCEPTION! querying :asn, url: :url, exception:\n :exception]", [
                'asn' => $asn,
                'url' => $url,
                'exception' => $e->getMessage()
            ]));
            return $emptyResult;
        }

        if (!$response->successful()) {
            Log::error(__("[ERROR! querying :asn, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'asn' => $asn,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }

        //Log::debug('status='. $response->status());

        $data = $response->json();

        // dump($data);

        if (!isset($data['data']['prefixes'])) {
            Log::error(__("[ERROR! querying :asn, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'asn' => $asn,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }
        //dd();

        $prefixes = [];

        foreach ($data['data']['prefixes'] as $prefix) {
            $prefixes[] = $prefix['prefix'];
        }

        $result = $prefixes;

        return $result;
    }


    public function searchRipeGetAs($ip): array
    {
        // dump('GET AS IP '.$asn);

        $url = 'https://stat.ripe.net/data/network-info/data.json?resource=' . $ip;

        $http = Http::withOptions([
            'allow_redirects' => true,
            'verify' => false
        ]);

        $emptyResult = [];

        try {
            $response = $http->get($url);
        } catch (Exception $e) {
            // Log::error('get host rdap.arin.net=' . gethostbyname('rdap.arin.net'));
            // Log::error('get host google.com=' . gethostbyname('google.com'));
            // Log::error('get host rdap.afrinic.net=' . gethostbyname('rdap.afrinic.net'));

            Log::error(__("[EXCEPTION! querying :ip, url: :url, exception:\n :exception]", [
                'ip' => $ip,
                'url' => $url,
                'exception' => $e->getMessage()
            ]));
            return $emptyResult;
        }

        if (!$response->successful()) {
            Log::error(__("[ERROR! querying :ip, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'ip' => $ip,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }

        //Log::debug('status='. $response->status());

        $data = $response->json();

        // dump($data);

        if (!isset($data['data']['asns'])) {
            Log::error(__("[ERROR! querying :ip, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'ip' => $ip,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }
        //dd();

        $prefixes = [];

        foreach ($data['data']['asns'] as $prefix) {
            $prefixes[] = $prefix;
        }

        $result = $prefixes;

        return $result;
    }

    public function searchArin($ip): array
    {
        //dump('WHOIS SEARCH '.$ip);

        if (stripos($ip, '/') !== false) {
            list($ips, $mask) = explode('/', $ip);
        } else {
            $ips = $ip;
            $mask = 32;

            if (stripos($ip, ':') !== false) {
                // dump('ipv6');
                $mask = 128;
            }
        }

        $test['ip'] = $ips;
        $test['cidr'] = $mask;

        $emptyResult = [

            'iplong' => (stripos($test['ip'], ':')!==false) ? bin2hex(inet_pton($test['ip'])): ip2long($test['ip']),
            'mask' => $test['cidr'],

            'date' => now(),

            'inetnum' => '',
            'range' => '',
            'netname' => '',
            'country' => '',
            'orgname' => '',
            'output' => json_encode(['response' => 'empty', 'had_error' => true])
        ];

        $url = 'https://rdap.arin.net/registry/ip/' . $ip;

        $http = Http::withOptions([
            'allow_redirects' => true,
            'verify' => false
        ]);

        try {
            $response = $http->get($url);
        } catch (Exception $e) {
            // Log::error('get host rdap.arin.net=' . gethostbyname('rdap.arin.net'));
            // Log::error('get host google.com=' . gethostbyname('google.com'));
            // Log::error('get host rdap.afrinic.net=' . gethostbyname('rdap.afrinic.net'));

            Log::error(__("[EXCEPTION! querying :ip, url: :url, exception:\n :exception]", [
                'ip' => $ip,
                'url' => $url,
                'exception' => $e->getMessage()
            ]));
            return $emptyResult;
        }

        if (!$response->successful()) {
            Log::error(__("[ERROR! querying :ip, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'ip' => $ip,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }

        //Log::debug('status='. $response->status());

        $data = $response->json();

        if ($data['port43'] == 'whois.afrinic.net') {
            //search again directly in afrinic
            $url = 'https://rdap.afrinic.net/rdap/ip/' . $ip;

            $response = $http->get($url);

            $data = $response->json();
        }

        //dump($data);
        //dump('whois data='. print_r($response, true));

        $start = $data['startAddress'] ?? 'start';
        $end = $data['endAddress'] ?? 'end';

        $data['cidr0_cidrs'] = $data['cidr0_cidrs'] ?? [];

        $cidr = [];

        if (isset($data['ipVersion'])) {
            $ipVersion = $data['ipVersion'];

            foreach ($data['cidr0_cidrs'] as $prefix) {
                $cidr[] = $prefix[$ipVersion . 'prefix'] . '/' . $prefix['length'];
            }
        }

        $cidrStr = implode(', ', $cidr);


            //$data['handle'] ?? 'handle';


        $netname = $data['name'] ?? ($data['handle'] ?? 'name');

        $orgname = 'not found';
        $country = $data['country'] ?? '';

        if (isset($data['remarks'][0]['description'][0])) {
            $orgname = $data['remarks'][0]['description'][0];
        }

        foreach ($data['entities'] as $entity) {
            if (isset($entity['vcardArray'])) {
                foreach ($entity['vcardArray'][1] as $row) {
                    if ($row[0] == 'fn') {
                        $orgname = $row[3];
                    }

                    if ($row[0] == 'adr' && !isset($data['country'])) {
                        if (!isset($row[1]['label'])) {
                            $country = array_pop($row[3]);
                        }
                    }
                }
                break;
            }
        }

        $country = empty($country) ? 'US' : strtoupper($country);


        $inetnum = $start.' - '.$end;
        $range = $start.' - '.$end;
        if (stripos($ip, ':') !== false && isset($data['handle'])) {
            $range = $data['handle'];
        }

        if (!empty($cidrStr)) {
            $inetnum = $start.' - '.$end." ($cidrStr)";
        }

        $result = [

            'iplong' => (stripos($test['ip'], ':')!==false) ? bin2hex(inet_pton($test['ip'])): ip2long($test['ip']),
            'mask' => $test['cidr'],

            'date' => now(),

            'inetnum' => substr($inetnum, 0, 190),
            'range' => $range,
            'netname' => $netname,
            'country' => $country,
            'orgname' => $orgname,
            'output' => json_encode($data)
        ];

        //dump($data);

        //dd();
        return $result;
    }
}
