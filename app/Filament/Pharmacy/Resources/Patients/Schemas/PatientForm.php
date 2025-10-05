<?php

namespace App\Filament\Pharmacy\Resources\Patients\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        $userCommunities = Filament::auth()->user()->communities()->pluck('name', 'id');

        return $schema
            ->components([
                Section::make('Assignment')
                    ->schema([
                        Select::make('community_id')
                            ->label('Assign to Community')
                            ->options($userCommunities)
                            ->required(),
                    ]),
                Section::make('Personal Information')
                    ->description('Basic demographic and contact details for the patient.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('full_name')->required()->maxLength(255),

                        TextInput::make('phone')->tel()->required()
                            ->unique(
                                table: User::class,
                                column: 'phone',
                                // --- THIS IS THE DEFINITIVE FIX ---
                                ignoreRecord: false, // Turn off automatic detection
                                modifyRuleUsing: fn (Unique $rule, ?Model $record) => $rule
                                    ->where('is_patient', true)
                                    // Manually tell the rule to ignore the user associated with the patient being edited
                                    ->ignore($record?->user_id)
                                // --- END OF FIX ---
                            )->validationMessages(['unique' => 'A patient profile with this phone number already exists.']),

                        TextInput::make('user.email')->email()
                            ->unique(
                                table: User::class,
                                column: 'email',
                                // --- THIS IS THE DEFINITIVE FIX ---
                                ignoreRecord: false, // Turn off automatic detection
                                modifyRuleUsing: fn (Unique $rule, ?Model $record) => $rule
                                    ->where('is_patient', true)
                                    // Manually tell the rule to ignore the user associated with the patient being edited
                                    ->ignore($record?->user_id)
                                // --- END OF FIX ---
                            )->hidden(fn (?Model $record): bool => $record !== null)->validationMessages(['unique' => 'A patient profile with this email address already exists.']),
                        DatePicker::make('date_of_birth')->label('Date of Birth')->maxDate(now()),
                        Select::make('gender')->options(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']),
                        TextInput::make('location_area')->label('Location / Area')->helperText('e.g., Lekki Phase 1, Ikeja GRA')->maxLength(255),
                    ]),

                Section::make('Health Snapshot')
                    ->description('An overview of the patient\'s current health status and medication regimen.')
                    ->schema([
                        Toggle::make('takes_regular_medications')
                            ->label('Does this patient take regular medications?')
                            ->live()
                            ->inline(false),

                        Textarea::make('medication_list')
                            ->label('List of Regular Medications')
                            ->helperText('Please list each medication and its dosage, one per line.')
                            ->rows(4)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('takes_regular_medications')),

                        CheckboxList::make('known_health_conditions')
                            ->label('Known Health Conditions')
                            ->options(Patient::getHealthConditionOptions()) // Using the helper from the model
                            ->columns(2),

                        Select::make('last_health_check')
                            ->label('Last Blood Pressure or Sugar Check')
                            ->options(Patient::getLastHealthCheckOptions()), // Using the helper from the model
                    ]),

                Section::make('Care & Engagement')
                    ->description('Information about the patient\'s healthcare habits and expectations.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('monthly_medicine_spend')
                                ->label('Estimated Monthly Medicine Spend')
                                ->options(Patient::getMonthlySpendOptions()),

                            Select::make('usual_purchase_location')
                                ->label('Where They Usually Buy Medicines')
                                ->options(Patient::getPurchaseLocationOptions()),
                        ]),

                        Select::make('expectations_from_pharmacist')
                            ->label('What does the patient expect from a pharmacist?')
                            ->options(Patient::getExpectationsOptions()),

                        Toggle::make('received_pharmacist_follow_up')
                            ->label('Has the patient ever received a pharmacist follow-up?')
                            ->inline(false),

                        Toggle::make('consents_to_contact')
                            ->label('Patient consents to be contacted by Careflux pharmacists.')
                            ->required()
                            ->inline(false),
                    ]),
            ]);
    }
}
