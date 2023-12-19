<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

use App\Helpers\Rbl4;

use App\Rules\CheckV4;
use App\Rules\SyncV4;

class StoreV4Request extends FormRequest
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

        $ipv4 = preg_split("/\n/",$this->ips);

        $exist = array();
        $data = array();

        //dump($ipv4);

        //trim each line
        foreach ($ipv4 as $line => $ip) {
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
        $lists4 = Rbl4::getLists();

        return [
            //
            'ips' => [
                'required'
            ],
            'eachIp.*.ip' => [
                function ($attribute, $value, $fail) {
                    $c4 = new CheckV4($this->input('list'));

                    $result = $c4->passes($attribute, $value);

                    //dump(__METHOD__.' '.__LINE__);
                    //dump($result);

                    if (!$result) {
                        $fail($c4->message());
                    } else {
                        // dump($c4->passOk);
                        $this->ipOk['resip'][] = $c4->passOk['resip'];
                        if (isset($c4->passOk['okip'])) {
                            $this->ipOk['okip'][] = $c4->passOk['okip'];
                        }
                    }
                }
            ],
            'list' => [
                'required',
                Rule::in($lists4),
                new SyncV4()
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
    public function validated(): array
    {
        $data = parent::validated();

        if (!isset($this->ipOk['okip'])) {
            $this->ipOk['okip'] = [];
        }

        $data = array_merge($data, [
            'resip' => $this->ipOk['resip'],
            'okip' => $this->ipOk['okip']
        ]);

        //dump(__METHOD__.' '.__LINE__);
        //dump($data);

        return $data;
    }
}
