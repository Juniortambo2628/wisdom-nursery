<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->json('channels'); // ["sms","whatsapp","email"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20); // sms | whatsapp | email
            $table->string('recipient');
            $table->string('event', 50);   // order_paid | quote_requested | order_ready | …
            $table->nullableMorphs('subject');
            $table->text('body')->nullable();
            $table->string('status', 20);  // sent | failed | logged
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('alert_recipients');
    }
};
