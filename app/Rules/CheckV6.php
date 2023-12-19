<?php

namespace App\Rules;

use App\Helpers\Rbl6;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckV6 implements Rule
{
    private $errorMsg;
    private $list;
    public $passOk;

    /**
     * Create a new rule instance.
     *
     * @param string|null $list
     * @return void
     */
    public function __construct(string $list = null)
    {
        //
        $this->list = $list;

        $this->errorMsg = trans('IPV6 invalid');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        //eachIp.0.ip
        $line = substr($attribute, 7);
        $line = substr($line, 0, -3);

        if (is_null($this>$this->list)) {
            return false;
        }
        // dump($line);

        // dump($attribute);

        // dump($value);

        $rblv6 = new Rbl6();

        // DB::enableQueryLog();
        $result = $rblv6->filterIp($value, $this->list);
        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        // dump(__METHOD__.' result=');
        // dump($result);

        $this->errorMsg = '"'.$value.'" is invalid.';

        if ($result['resip']['ip'] == -1) {
            //error
            $this->errorMsg = __('line :line, ip ":ip", error: :error', ['line' => ($line + 1), 'ip' => $value, 'error' => $result['resip']['error']]);
        }

        if ($result['resip']['ip'] == -2) {
            //exists
            $this->errorMsg = __('line :line, ip ":ip", error: :error', ['line' => ($line + 1), 'ip' => $value, 'error' => $result['resip']['error']]);
            // dump($result);

            $this->passOk = [
                'resip' => $result['resip'],
                // 'okip' => $result['okip']
            ];

            return true;
        }

        if (isset($result['okip']) && !empty($result['okip'])) {
            //

            $this->passOk = [
                'resip' => $result['resip'],
                'okip' => $result['okip']
            ];

            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->errorMsg;
    }
}
