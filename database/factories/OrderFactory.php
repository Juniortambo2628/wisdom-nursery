<?php

namespace Database\Factories;

use App\Enums\Fulfilment;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'customer_phone' => '254712345678',
            'fulfilment' => Fulfilment::Collect,
            'subtotal' => 3200,
            'total' => 3200,
            'status' => OrderStatus::PendingPayment,
        ];
    }
}
