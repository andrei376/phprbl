<?php

namespace App\Http\Requests;

use App\Helpers\Rbl6;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

use App\Rules\CheckV6;
use App\Rules\SyncV6;

class StoreV6Request extends FormRequest
{
    private $ipErrors;
    private $ipOk;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     * @noinspection PhpUnused
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @noinspection PhpUnused
     */
    protected function prepareForValidation()
    {
        //$ips=preg_split("/\n/",$this->data['Rbl']['ips']);

        //split textarea lines

        $ipv6 = preg_split("/\n/",$this->ips);

        $exist = array();
        $data = array();

        // dump($ipv6);

        //trim each line
        foreach ($ipv6 as $line => $ip) {
            $saveIp = trim($ip);

            // if same ip is listed many times in textarea
            if (in_array($saveIp, $exist)) {
                $this->ipErrors['eachIp.'.$saveIp.'.ip'] = __('line :line, ":ip" already in list', ['line' => ($line + 1), 'ip' => $saveIp]);
            }

            $exist[] = $saveIp;
            $data[] = array('ip' => $saveIp);
        }

        //add to the validation checks
        $this->merge([
            'eachIp' => $data,
        ]);
    }

    /**
     * @param Validator $validator
     * @noinspection PhpUnused
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!empty($this->ipErrors)) {
                foreach ($this->ipErrors as $field => $value) {
                    $validator->errors()->add($field, $value);
                }
            }
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @noinspection PhpUnused
     *
     */
    public function rules(): array
    {
        $lists6 = Rbl6::getLists();

        return [
            //
            'ips' => [
                'required'
            ],
            'eachIp.*.ip' => [
                function ($attribute, $value, $fail) {
                    $c6 = new CheckV6($this->input('list'));

                    $result = $c6->passes($attribute, $value);

                    // dump(__METHOD__.' '.__LINE__);
                    // dump($result);

                    if (!$result) {
                        $fail($c6->message());
                    } else {
                        // dump($c6->passOk);
                        $this->ipOk['resip'][] = $c6->passOk['resip'];
                        if (isset($c6->passOk['okip'])) {
                            $this->ipOk['okip'][] = $c6->passOk['okip'];
                        }
                    }
                }
            ],
            'list' => [
                'required',
                Rule::in($lists6),
                new SyncV6()
                //check list is synced
            ]
        ];
    }


    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function messages(): array
    {
        return [
            'ips.required' => __('Please enter some IPs.'),
            'list.required' => __('Please select list.'),
        ];
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        if (!isset($this->ipOk['okip'])) {
            $this->ipOk['okip'] = [];
        }

        $data = array_merge($data, [
            'resip' => $this->ipOk['resip'],
            'okip' => $this->ipOk['okip']
        ]);

        // dump(__METHOD__.' '.__LINE__);
        // dump($data);

        return $data;
    }
}
