<?php

namespace App\Filament\Resources\PayoutRequests\Tables;

use App\Models\PayoutRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Src\Wallet\Application\Services\WalletService;

class PayoutRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wallet.owner.name')
                    ->label('User')
                    ->searchable(),

                TextColumn::make('amount_kobo')
                    ->label('Amount')
                    ->money('NGN', 100)
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('process_payout')
                    ->label('Process')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Process Payout Request')
                    ->schema([
                        TextEntry::make('details')
                            ->state(function (PayoutRequest $record): HtmlString {
                                $bank = $record->bankAccount;

                                if (! $bank) {
                                    return new HtmlString(
                                        '<p class="text-danger-600 font-semibold">
                                            Error: Bank account details not found for this payout request.
                                        </p>'
                                    );
                                }

                                return new HtmlString(
                                    'You are about to process a payout of 
                                    <strong>₦'.number_format($record->amount_kobo / 100, 2).'</strong> to:<br><br>'.
                                    "<strong>Bank:</strong> {$bank->bank_name}<br>".
                                    "<strong>Account Name:</strong> {$bank->account_name}<br>".
                                    "<strong>Account Number:</strong> {$bank->account_number}"
                                );
                            }),

                        Radio::make('action')
                            ->label('Decision')
                            ->options([
                                'completed' => 'Mark as Completed',
                                'rejected' => 'Reject Request',
                            ])
                            ->required()
                            ->live(),

                        Textarea::make('admin_notes')
                            ->label('Notes / Rejection Reason')
                            ->requiredIf('action', 'rejected'),
                    ])
                    ->action(function (
                        PayoutRequest $record,
                        array $data,
                        WalletService $walletService
                    ) {
                        DB::transaction(function () use ($record, $data, $walletService) {
                            if ($data['action'] === 'completed') {
                                $walletService->debit(
                                    $record->wallet->owner,
                                    $record->amount_kobo,
                                    'Payout completed by admin.',
                                    $record
                                );

                                $record->update([
                                    'status' => 'completed',
                                    'processed_by_admin_id' => Auth::id(),
                                    'admin_notes' => $data['admin_notes'] ?? null,
                                ]);

                                Notification::make()
                                    ->title('Payout Marked as Completed')
                                    ->success()
                                    ->send();
                            } else {
                                $record->update([
                                    'status' => 'rejected',
                                    'processed_by_admin_id' => Auth::id(),
                                    'admin_notes' => $data['admin_notes'],
                                ]);

                                Notification::make()
                                    ->title('Payout Request Rejected')
                                    ->warning()
                                    ->send();
                            }
                        });
                    })
                    ->visible(fn (PayoutRequest $record) => $record->status === 'pending'),

                EditAction::make()
                    ->visible(fn (PayoutRequest $record) => $record->status !== 'completed'),
            ])
            ->toolBarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
