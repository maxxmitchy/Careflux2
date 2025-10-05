<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('author_name')->required(),
                    TextInput::make('author_location')->required(),
                    Select::make('rating')->options(array_combine(range(1, 5), range(1, 5)))->required()->default(5),
                    FileUpload::make('author_image')->image()->disk('public')->directory('testimonials'),
                    Textarea::make('quote')->required()->columnSpanFull(),
                    Toggle::make('is_featured')->label('Feature this testimonial on the homepage?'),
                ])->columns(2),
            ]);
    }
}
