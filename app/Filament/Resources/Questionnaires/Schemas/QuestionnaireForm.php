<?php

namespace App\Filament\Resources\Questionnaires\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class QuestionnaireForm
{
    public static function configure(Schema $schema, bool $isAdmin = true): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')->required()->columnSpanFull(),
                        Textarea::make('description')->columnSpanFull(),
                        Toggle::make('is_template')
                            ->label('Is this a global template?')
                            ->helperText('Templates can be used by all pharmacists.')
                            ->visible($isAdmin),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive templates won\'t be visible to pharmacists.')
                            ->visible($isAdmin),
                    ]),

                Section::make('Questions')
                    ->schema([
                        // Root question repeater starts at depth 0
                        self::getQuestionRepeater('questions', 'Add Question', depth: 0, maxDepth: 2),
                    ]),
            ]);
    }

    /**
     * Recursive helper for generating nested question repeaters.
     *
     * @param  string  $relationshipName  The relationship name in the model.
     * @param  string  $addActionLabel  The label for the "Add" button.
     * @param  int  $depth  Current nesting depth.
     * @param  int  $maxDepth  Maximum nesting depth allowed.
     */
    private static function getQuestionRepeater(
        string $relationshipName,
        string $addActionLabel,
        int $depth = 0,
        int $maxDepth = 2
    ): Repeater {
        return Repeater::make($relationshipName)
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
                    ->live(),

                Repeater::make('answerOptions')
                    ->label('Answer Options')
                    ->schema(array_filter([
                        TextInput::make('text')->required()->label('Option Text'),

                        // Only add nested child questions if depth < maxDepth
                        $depth < $maxDepth
                            ? self::getQuestionRepeater(
                                'childQuestions',
                                'Add Follow-up Question',
                                $depth + 1,
                                $maxDepth
                            )
                            : null,
                    ]))
                    ->columnSpanFull()
                    ->addActionLabel('Add Answer Option')
                    ->reorderableWithDragAndDrop('order')
                    ->cloneable()
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (Get $get): bool => in_array($get('type'), ['radio', 'checkbox'])),
            ])
            ->addActionLabel($addActionLabel)
            ->reorderableWithDragAndDrop('order')
            ->cloneable()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['text'] ?? null);
    }
}
