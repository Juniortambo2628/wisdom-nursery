<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class AlertRecipient extends Model
{
    use Notifiable;

    public const CHANNELS = ['sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'email' => 'Email'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['channels' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function wants(string $channel): bool
    {
        return in_array($channel, $this->channels ?? [], true);
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
