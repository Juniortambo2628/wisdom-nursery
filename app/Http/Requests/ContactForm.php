<?php

namespace App\Http\Requests;

use App\Rules\KenyanPhone;

class ContactForm extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', new KenyanPhone],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
