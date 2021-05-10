<?php

namespace App\Rules;

use App\Models\DefineList;
use Illuminate\Contracts\Validation\Rule;

class SyncV6 implements Rule
{
    private $errorMsg;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        //
        //dump($attribute);
        //dump($value);

        $result = DefineList::isSync($value);

        //dump($result);

        $this->errorMsg = __(":list is not synchronized", ['list' => $value]);

        return $result;
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
