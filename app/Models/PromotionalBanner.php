<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionalBanner extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'promotional_banners';

    /**
     * The attributes that are mass assignable.
     *
     * These are the fields that can be filled when using the Create/Edit form
     * in your Filament PromotionalBannerResource.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'text_content',
        'button_text',
        'button_url',
        'images',
        'is_active',
        'placement',
        'display_after_item',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * This is crucial for handling the 'images' JSON data, the 'is_active'
     * boolean flag, and the integer for display order correctly.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // This automatically converts the JSON string from the database
        // into a PHP array when you access $banner->images, and back
        // to JSON when you save it.
        'images' => 'array',

        // This ensures 'is_active' is always a true/false boolean.
        'is_active' => 'boolean',

        // This ensures 'display_after_item' is always an integer.
        'display_after_item' => 'integer',
    ];
}
