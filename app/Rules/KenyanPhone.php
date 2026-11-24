<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KenyanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Phone::normalise($value) === null) {
            $fail('Enter a Safaricom or Airtel number, like 0712 345 678.');
        }
    }
}
