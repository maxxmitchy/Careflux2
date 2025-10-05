<?php

namespace App\Filament\Pharmacy\Resources\Questionnaires\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class QuestionnaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Questionnaire Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Questions')
                    ->description('Build the questions for your form. You can add nested follow-up questions under each answer option for radio or checkbox types.')
                    ->schema([
                        self::getQuestionRepeater(),
                    ]),
            ]);
    }

    private static function getQuestionRepeater(): Repeater
    {
        return Repeater::make('questions') // This is now just a key in an array
            ->schema([
                TextInput::make('text')
                    ->required()
                    ->label('Question Text')
                    ->columnSpanFull(),

                Select::make('type')
                    ->options([
                        'radio' => 'Radio (Select One)',
                        'checkbox' => 'Checkbox (Select Many)',
                        'text' => 'Text (Short Answer)',
                        'textarea' => 'Text Area (Long Answer)',
                    ])
                    ->required()
                    ->live(), // Makes the form reactive

                // Nested Repeater for Answer Options
                Repeater::make('answerOptions')
                    ->label('Answer Options')
                    ->schema([
                        TextInput::make('text')->required()->label('Option Text'),

                        // The recursive, nested part for follow-up questions
                        Section::make('Follow-up Questions')
                            ->description('Add questions that only appear if this answer is selected.')
                            ->schema([
                                self::getNestedQuestionRepeater('childQuestions'),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpanFull()
                    ->addActionLabel('Add Answer Option')
                    ->reorderableWithDragAndDrop()
                    ->cloneable()
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['radio', 'checkbox'])),
            ])
            ->addActionLabel('Add Top-Level Question')
            ->reorderableWithDragAndDrop()
            ->cloneable()
            ->collapsible();
    }

    /**
     * A separate helper for nested questions to avoid potential conflicts.
     * It is identical in structure but uses a different key ('childQuestions').
     */
    private static function getNestedQuestionRepeater(string $key): Repeater
    {
        return Repeater::make($key)
            ->schema([
                TextInput::make('text')->required()->label('Question Text')->columnSpanFull(),
                Select::make('type')->options(['radio' => 'Radio (Select One)', 'checkbox' => 'Checkbox (Select Many)', 'text' => 'Text (Short Answer)', 'textarea' => 'Text Area (Long Answer)'])->required()->live(),
                Repeater::make('answerOptions')
                    ->label('Answer Options')
                    ->schema([
                        TextInput::make('text')->required()->label('Option Text'),
                    ])
                    ->columnSpanFull()
                    ->addActionLabel('Add Answer Option')
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['radio', 'checkbox'])),
            ])
            ->addActionLabel('Add Follow-up Question');
    }
}
