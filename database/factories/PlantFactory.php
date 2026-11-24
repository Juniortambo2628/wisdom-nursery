<?php

namespace Database\Factories;

use App\Enums\Availability;
use App\Enums\SoldAs;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlantFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'sold_as' => SoldAs::Potted,
            'price' => 1600,
            'stock_qty' => 10,
            'availability' => Availability::Ready,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock_qty' => 0, 'availability' => Availability::OutOfStock]);
    }

    public function readyInWeeks(int $weeks): static
    {
        return $this->state(['availability' => Availability::ReadyOn, 'ready_on' => now()->addWeeks($weeks)]);
    }
}
