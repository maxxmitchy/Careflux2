<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class PharmacistReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('week_ending_date')
                            ->label('Week Ending On')
                            ->required()
                            ->default(fn () => Carbon::now()->endOfWeek()->format('Y-m-d'))
                            ->displayFormat('F j, Y')
                            ->native(false) // Use a better calendar picker
                            ->disabledOn('edit'),

                        Textarea::make('biggest_win')
                            ->label('What was your biggest win this week?')
                            ->helperText('Share a success story or a positive patient interaction.')
                            ->rows(3)
                            ->required(),

                        Textarea::make('biggest_challenge')
                            ->label('What was your biggest challenge?')
                            ->helperText('What obstacles did you face? e.g., medication availability, patient compliance.')
                            ->rows(3)
                            ->required(),

                        Textarea::make('at_risk_patients')
                            ->label('Are any of your patients at risk?')
                            ->helperText('Mention any patients you feel might be struggling or at risk of disengaging.')
                            ->rows(3),

                        Textarea::make('support_needed')
                            ->label('What support do you need from the Careflux team?')
                            ->helperText('Let us know how we can help you succeed.')
                            ->rows(3),
                    ]),
            ]);
    }
}
