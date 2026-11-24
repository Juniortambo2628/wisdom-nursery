<?php

namespace App\Http\Requests;

use App\Models\DeliveryArea;
use App\Rules\KenyanPhone;
use Illuminate\Validation\Rule;

class CheckoutForm extends PublicFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', new KenyanPhone],
            'fulfilment' => ['required', Rule::in(['collect', 'delivery'])],
            'delivery_area_id' => ['required_if:fulfilment,delivery', 'nullable',
                Rule::exists(DeliveryArea::class, 'id')->where('is_active', true)],
            'delivery_address' => ['required_if:fulfilment,delivery', 'nullable', 'string', 'max:255'],
            'mpesa_phone' => ['required', new KenyanPhone],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_area_id.required_if' => 'Choose your delivery area.',
            'delivery_address.required_if' => 'Tell us where to deliver.',
        ];
    }
}
