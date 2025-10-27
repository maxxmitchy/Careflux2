<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Patient Details')
                    ->columns(2)
                    ->schema([
                        Select::make('community_id')
                            ->relationship('community', 'name')
                            ->searchable()->preload()->required()->live(),
                        Select::make('pharmacist_id')
                            ->label('Assign to Pharmacist')
                            ->options(function (Get $get) {
                                $communityId = $get('community_id');
                                if (! $communityId) {
                                    return [];
                                }

                                return \Src\Pharmacy\Domain\Models\Community::find($communityId)->users()->pluck('name', 'id');
                            })
                            ->searchable()->preload()->required(),
                        TextInput::make('full_name')->required(),
                        TextInput::make('phone')->tel()->required()
                            ->unique(
                                table: User::class, column: 'phone', ignoreRecord: true,
                                modifyRuleUsing: fn (Rule $rule) => $rule->where(fn ($query) => $query->whereHas('patientProfile'))
                            )->validationMessages(['unique' => 'A patient profile with this phone number already exists.']),
                        TextInput::make('email')->email()
                            ->unique(
                                table: User::class, column: 'email', ignoreRecord: true,
                                modifyRuleUsing: fn (Rule $rule) => $rule->where(fn ($query) => $query->whereHas('patientProfile'))
                            )->validationMessages(['unique' => 'A patient profile with this email address already exists.']),
                        DatePicker::make('date_of_birth'),
                        Select::make('gender')->options(['Male', 'Female', 'Other']),
                        TextInput::make('location_area'),
                    ]),
                Section::make('Health & Lifestyle')
                    ->schema([
                        Toggle::make('takes_regular_medications')->inline(false)->columnSpanFull(),
                        Textarea::make('medication_list')
                            ->label('List of Regular Medications')
                            ->visible(fn (Get $get) => $get('takes_regular_medications')),
                        CheckboxList::make('known_health_conditions')
                            ->options(Patient::getHealthConditionOptions()), // Assumes a static helper on the Patient model
                        Select::make('last_health_check')->options(Patient::getLastHealthCheckOptions()),
                        Select::make('monthly_medicine_spend')->options(Patient::getMonthlySpendOptions()),
                        Select::make('usual_purchase_location')->options(Patient::getPurchaseLocationOptions()),
                        Toggle::make('received_pharmacist_follow_up')->inline(false),
                        Select::make('expectations_from_pharmacist')->options(Patient::getExpectationsOptions()),
                        Toggle::make('consents_to_contact')->inline(false)->required(),
                    ]),
            ]);
    }
}
