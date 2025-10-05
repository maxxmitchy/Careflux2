<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These are the fields that can be filled when using the Create/Edit form
     * in your Filament TestimonialResource.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_name',
        'author_location',
        'quote',
        'author_image',
        'rating',
        'is_featured',
    ];

    /**
     * The attributes that should be cast to native types.
     * This ensures data integrity when retrieving from and saving to the database.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // Ensures the rating is always an integer (number).
        'rating' => 'integer',

        // Ensures 'is_featured' is always a true/false boolean.
        'is_featured' => 'boolean',
    ];
}
