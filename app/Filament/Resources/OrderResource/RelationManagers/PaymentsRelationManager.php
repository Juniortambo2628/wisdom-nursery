<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Mpesa\PaymentService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('j M, H:i')->label('When'),
                Tables\Columns\TextColumn::make('method')->formatStateUsing(fn ($state) => $state === 'stk' ? 'M-Pesa prompt' : 'Code entered'),
                Tables\Columns\TextColumn::make('amount')->formatStateUsing(fn ($state) => ksh($state)),
                Tables\Columns\TextColumn::make('mpesa_receipt')->label('M-Pesa code')->copyable()->placeholder('-'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => str_replace('_', ' ', ucfirst($state->value)))
                    ->color(fn (PaymentStatus $state) => match ($state) {
                        PaymentStatus::Success => 'success',
                        PaymentStatus::PendingVerification => 'warning',
                        PaymentStatus::Failed, PaymentStatus::Rejected, PaymentStatus::Cancelled => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('result_desc')->label('Details')->wrap()->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')->label('Confirm payment')->icon('heroicon-o-check')->color('success')->button()
                    ->visible(fn (Payment $p) => $p->status === PaymentStatus::PendingVerification)
                    ->requiresConfirmation()
                    ->modalDescription(fn (Payment $p) => 'Only confirm after checking '.($p->mpesa_receipt ?: 'this payment').' for '.ksh($p->amount).' in the M-Pesa statement. The order is marked paid and the customer gets an SMS.')
                    ->action(function (Payment $p, PaymentService $payments) {
                        $p->update(['verified_by' => auth()->id(), 'processed_at' => null]);
                        $payments->markPaid($p, $p->mpesa_receipt, $p->order->total, null, 0, 'Confirmed by '.auth()->user()->name);
                        Notification::make()->title('Payment confirmed')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')->label('Reject')->color('danger')
                    ->visible(fn (Payment $p) => $p->status === PaymentStatus::PendingVerification)
                    ->requiresConfirmation()
                    ->action(fn (Payment $p) => $p->update(['status' => PaymentStatus::Rejected, 'verified_by' => auth()->id(), 'processed_at' => now()])),
            ]);
    }
}
