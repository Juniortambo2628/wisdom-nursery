<?php

namespace App\Http\Requests;

use App\Rules\KenyanPhone;

class QuoteRequestForm extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', new KenyanPhone],
            'email' => ['nullable', 'email:rfc', 'max:160'],
            'organisation' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            // Free-text plants line from the home band, used when the quote list is empty.
            'plants' => ['nullable', 'string', 'max:300'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
