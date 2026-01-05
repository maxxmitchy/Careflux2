<?php

namespace App\Filament\Pharmacy\Pages;

use App\Models\BankAccount;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

class ManageBankAccounts extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected string $view = 'filament.pharmacy.pages.manage-bank-accounts';

    protected static ?string $navigationLabel = 'Bank Accounts';

    protected static string|UnitEnum|null $navigationGroup = 'Management & Reporting';

    protected static ?int $navigationSort = 11;

    public function table(Table $table): Table
    {
        return $table
            ->query(auth()->user()->bankAccounts()->getQuery())
            ->columns([
                TextColumn::make('bank_name'),
                TextColumn::make('account_name'),
                TextColumn::make('account_number')->formatStateUsing(fn (string $state) => '****'.substr($state, -4)),
                IconColumn::make('is_primary')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(BankAccount::class)
                    ->mutateDataUsing(function (array $data): array {
                        $data['owner_id'] = auth()->id();
                        $data['owner_type'] = auth()->user()->getMorphClass();

                        return $data;
                    })
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')->required(),
                        Forms\Components\TextInput::make('account_name')->required(),
                        Forms\Components\TextInput::make('account_number')->required()->numeric()->length(10),
                    ]),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }
}
