<?php

namespace App\Filament\Resources\PayoutRequests\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Src\Wallet\Application\Services\WalletService;

class PayoutRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wallet.owner.name')->label('User')->searchable(),
                TextColumn::make('amount_kobo')->money('NGN', 100)->label('Amount')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                Action::make('process')
                    ->label('Process Payout')
                    ->modalHeading('Process Payout Request')
                    ->schema([
                        TextEntry::make('details')
                            ->state(function (\App\Models\PayoutRequest $record): HtmlString {
                                $bank = $record->bankAccount;

                                return new HtmlString(
                                    'You are about to process a payout of <strong>₦'.number_format($record->amount_kobo / 100, 2).'</strong> to:<br><br>'.
                                    "<strong>Bank:</strong> {$bank->bank_name}<br>".
                                    "<strong>Account Name:</strong> {$bank->account_name}<br>".
                                    "<strong>Account Number:</strong> {$bank->account_number}"
                                );
                            }),
                        Radio::make('action')
                            ->options(['completed' => 'Mark as Completed', 'rejected' => 'Reject Request'])
                            ->required()->live(),
                        Textarea::make('admin_notes')->label('Notes / Rejection Reason')
                            ->required(fn ($get) => $get('action') === 'rejected'),
                    ])
                    ->action(function (\App\Models\PayoutRequest $record, array $data, WalletService $walletService) {
                        if ($data['action'] === 'completed') {
                            // Logic to debit the wallet
                            $walletService->debit($record->wallet->owner, $record->amount_kobo, 'Payout completed by admin.', $record);
                            $record->update(['status' => 'completed', 'processed_by_admin_id' => Auth::id(), 'admin_notes' => $data['admin_notes']]);
                            Notification::make()->title('Payout Marked as Completed')->success()->send();
                        } else {
                            $record->update(['status' => 'rejected', 'processed_by_admin_id' => Auth::id(), 'admin_notes' => $data['admin_notes']]);
                            Notification::make()->title('Payout Request Rejected')->warning()->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
