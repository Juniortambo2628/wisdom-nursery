<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('summary')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('botanical_name')->nullable();
            $table->string('sold_as', 20)->default('potted'); // potted | per_plant | seedling | tray
            $table->unsignedInteger('price');                 // whole KES
            $table->unsignedInteger('set_size')->nullable();  // "buy as: set of N"
            $table->unsignedInteger('set_price')->nullable();
            $table->unsignedInteger('bulk_min_qty')->nullable(); // at or above this, use the quote list
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('availability', 20)->default('ready'); // ready | ready_on | out_of_stock
            $table->date('ready_on')->nullable();
            $table->text('description')->nullable();
            $table->string('light')->nullable();
            $table->string('water')->nullable();
            $table->string('pot_size')->nullable();
            $table->string('grows_to')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_ref')->nullable()->unique(); // id in the Wisdom Capital import, for idempotent re-runs
            $table->timestamps();

            $table->index(['is_active', 'category_id', 'sort_order']);
        });

        Schema::create('plant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // "Goes well with" on plant pages.
        Schema::create('plant_pairings', function (Blueprint $table) {
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paired_plant_id')->constrained('plants')->cascadeOnDelete();
            $table->primary(['plant_id', 'paired_plant_id']);
        });

        Schema::create('stock_alerts', function (Blueprint $table) { // "Notify me" on out-of-stock plants
            $table->id();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone', 20);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->unique(['plant_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('plant_pairings');
        Schema::dropIfExists('plant_images');
        Schema::dropIfExists('plants');
        Schema::dropIfExists('categories');
    }
};
