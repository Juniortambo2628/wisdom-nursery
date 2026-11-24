<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::firstOrCreate(['author' => '[Customer name]'], [
            'quote' => '[Customer testimonial]',
            'context' => '[Organisation or town]',
        ]);
    }
}
