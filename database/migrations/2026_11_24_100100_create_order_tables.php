<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('fee'); // KES
            $table->string('eta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique()->nullable(); // WN-1001…, set right after insert from the id
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->string('fulfilment', 20); // collect | delivery
            $table->foreignId('delivery_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('delivery_area_name')->nullable(); // snapshot
            $table->string('delivery_address')->nullable();
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('total');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('pending_payment');
            // pending_payment | paid | ready | dispatched | collected | delivered | cancelled
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('public_token', 40)->unique(); // lets the customer's browser poll its own order
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plant_name'); // snapshot
            $table->string('buy_as', 20)->default('single'); // single | set
            $table->unsignedInteger('units_per_item')->default(1);
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('line_total');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20); // stk | manual_code
            $table->string('phone', 20)->nullable();
            $table->unsignedInteger('amount');
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('merchant_request_id')->nullable();
            $table->string('mpesa_receipt')->nullable()->index();
            $table->integer('result_code')->nullable();
            $table->string('result_desc')->nullable();
            $table->json('raw_callback')->nullable();
            $table->string('status', 30)->default('pending');
            // pending | success | failed | cancelled | pending_verification | rejected
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('organisation')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('new'); // new | replied | won | lost
            $table->timestamps();
        });

        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plant_name');
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('delivery_areas');
    }
};
