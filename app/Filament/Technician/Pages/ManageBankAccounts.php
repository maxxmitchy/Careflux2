<?php

namespace App\Filament\Technician\Pages;

use App\Models\BankAccount;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ManageBankAccounts extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'My Bank Accounts';

    protected static string|UnitEnum|null $navigationGroup = 'Financials';

    protected static ?int $navigationSort = 10;

    // Ensure this view exists in resources/views/filament/technician/pages/manage-bank-accounts.blade.php
    // It should contain: <x-filament-panels::page> {{ $this->table }} </x-filament-panels::page>
    protected string $view = 'filament.technician.pages.manage-bank-accounts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Retrieve accounts belonging specifically to the logged-in technician
                Auth::user()->bankAccounts()->getQuery()
            )
            ->columns([
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable(),

                TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->copyable() // Handy for quick copying
                    ->formatStateUsing(fn (string $state) => '****'.substr($state, -4)),

                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Bank Account')
                    ->modalHeading('Add New Bank Details')
                    ->model(BankAccount::class)
                    ->mutateDataUsing(function (array $data): array {
                        // Polymorphic association to the logged-in technician
                        $data['owner_id'] = Auth::id();
                        $data['owner_type'] = Auth::user()->getMorphClass();

                        return $data;
                    })
                    ->schema($this->getFormSchema()),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema($this->getFormSchema()),
                DeleteAction::make(),
            ]);
    }

    /**
     * Reusable schema for Create and Edit actions
     */
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('bank_name')
                ->label('Bank Name')
                ->placeholder('e.g. GTBank, Zenith Bank')
                ->required()
                ->autocomplete(),

            Forms\Components\TextInput::make('account_name')
                ->label('Account Name')
                ->helperText('Ensure this matches your registered name.')
                ->required(),

            Forms\Components\TextInput::make('account_number')
                ->label('NUBAN / Account Number')
                ->required()
                ->numeric()
                ->length(10), // Standard Nigerian NUBAN length

            Forms\Components\Toggle::make('is_primary')
                ->label('Set as Primary Account')
                ->helperText('Salary will be paid to this account.')
                ->default(false),
        ];
    }
}
