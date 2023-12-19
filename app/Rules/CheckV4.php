<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Helpers\Rbl4;

class CheckV4 implements Rule
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

        $this->errorMsg = trans('IPV4 invalid');
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
        //dump($line);

        //dump($attribute);

        //dump($value);

        $rblv4 = new Rbl4();

        $result = $rblv4->filterIp($value, $this->list);

        //dump(__METHOD__.' result=');
        //dump($result);

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
