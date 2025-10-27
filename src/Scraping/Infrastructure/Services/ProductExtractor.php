<?php

namespace Src\Scraping\Infrastructure\Services;

use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class ProductExtractor
{
    /**
     * Safely extracts text from a node.
     */
    public function extractText(Crawler $node, string $selector, string $default = ''): string
    {
        try {
            return trim($node->filter($selector)->text($default));
        } catch (Throwable) {
            return $default;
        }
    }

    /**
     * Safely extracts an attribute from a node.
     */
    public function extractAttr(Crawler $node, string $selector, string $attribute): ?string
    {
        try {
            return $node->filter($selector)->attr($attribute);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Extracts a price string (e.g., "₦1,500.00") into a clean integer (150000 for kobo).
     */
    public function extractPrice(Crawler $node, string $selector): ?int
    {
        $priceText = $this->extractText($node, $selector);

        if (empty($priceText)) {
            return null;
        }

        // Remove currency symbols, commas, and other non-numeric characters
        $cleanedPrice = preg_replace('/[^0-9.]/', '', $priceText);

        if (! is_numeric($cleanedPrice)) {
            return null;
        }

        // Convert to kobo (or the smallest currency unit)
        return (int) (floatval($cleanedPrice) * 100);
    }

    public function resolveUrl(string $href, string $baseUrl): string
    {
        if (str_starts_with($href, 'http')) {
            return $href;
        }

        $base = parse_url($baseUrl);
        $baseDomain = $base['scheme'].'://'.$base['host'];

        $basePath = trim($base['path'] ?? '', '/');
        $hrefPath = ltrim($href, '/');

        // Remove duplication if href already includes the base path
        if ($basePath !== '' && $basePath !== '0' && str_starts_with($hrefPath, $basePath)) {
            $hrefPath = substr($hrefPath, strlen($basePath));
            $hrefPath = ltrim($hrefPath, '/'); // Clean leading slash after trim
        }

        return rtrim($baseDomain, '/').'/'.$hrefPath;
    }

    public function extractPriceText(?string $text): ?float
    {
        if ($text === null || $text === '' || $text === '0') {
            return null;
        }

        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = str_ireplace([','], '', trim($text));

        if (preg_match('/(?:USD|\$|£|EUR)?\s?(\d+(?:\.\d{2})?)/i', $text, $match)) {
            return (int) (floatval($match[1]) * 100);
        }

        return null;
    }

    /**
     * Return the first non-empty value among several attributes.
     *
     * @param  array<int,string>  $attrs  Attributes in priority order (e.g. ['src','data-src','data-srcset'])
     */
    public function firstAttr(Crawler $crawler, string $selector, array $attrs): ?string
    {
        foreach ($attrs as $attr) {
            $value = $this->extractAttr($crawler, $selector, $attr);
            if ($value) {
                // data-srcset values are "url width", we just want the URL
                if ($attr === 'data-srcset') {
                    return trim(explode(' ', $value)[0], ',');
                }

                return $value;
            }
        }

        return null;
    }

    /**
     * Extract a background-image URL from an inline style string.
     *
     * Example: background-image:url('https://example.com/image.jpg')
     */
    public function extractBackgroundImageUrl(?string $style): ?string
    {
        if (empty($style)) {
            return null;
        }

        // Regex to capture the value inside url(...)
        if (preg_match('/url\((["\']?)(.*?)\1\)/i', $style, $matches)) {
            return $matches[2] ?? null;
        }

        return null;
    }
}
