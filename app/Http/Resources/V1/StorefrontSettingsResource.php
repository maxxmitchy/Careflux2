<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorefrontSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        Log::info('Transforming storefront settings resource: '.json_encode($this->resource));

        // The $this->resource here is the `theme_settings` array from the Pharmacy model.
        return [
            'themeKey' => $this->resource['theme_key'] ?? 'default',

            'content' => [
                'heroHeadline' => $this->resource['hero_headline'] ?? 'Your Health, Our Priority.',
                'aboutUsText' => $this->resource['about_us_text'] ?? '',
            ],

            'appearance' => [
                'primaryColor' => $this->resource['primary_color'] ?? '#0D9488',
                'heroImageUrl' => isset($this->resource['hero_image'])
                    ? Storage::disk('public')->url($this->resource['hero_image'])
                    : null,
            ],

            'customPages' => collect($this->resource['custom_pages'] ?? [])->map(function ($page) {
                return [
                    'title' => $page['title'] ?? '',
                    'slug' => $page['slug'] ?? '',
                    'content' => $page['content'] ?? '',
                ];
            })->values(),

            'meta' => [
                'transformedAt' => now()->toISOString(),
                'version' => '1.0',
            ],
        ];
    }
}
