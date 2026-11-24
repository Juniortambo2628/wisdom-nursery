<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Base for public forms: honeypot field "website" must stay empty, and the
 * form must not be submitted faster than a person could fill it in.
 */
abstract class PublicFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $tooFast = $this->filled('_ts') && (time() - (int) $this->input('_ts')) < 2;

        if ($this->filled('website') || $tooFast) {
            throw ValidationException::withMessages(['website' => 'Please try again.']);
        }
    }
}
