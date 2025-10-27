<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Location\Domain\Models\Country;
use Src\Store\Domain\Models\Store;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding scraper store configurations...');

        $nigeria = Country::where('short_code', 'NG')->first();
        if (! $nigeria) {
            $this->command->error('Nigeria not found in countries table. Please run LocationSeeder first.');

            return;
        }

        $stores = [
            [
                'name' => 'Teeka 4', 'platform' => 'teeka4', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://teeka4.com/?s={query}&post_type=product',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\TeekaScraper',
                'requires_javascript' => true, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'MediGlow',
                'platform' => 'mediglow',
                'country_id' => $nigeria->id,
                'search_url_template' => 'https://mediglow.ng/?s={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\MediGlowScraper',
                'requires_javascript' => true,
                'paginate_type' => '/',
                'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Beyond Medplus', 'platform' => 'medplus', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://beyondmedplus.com/search?keyword={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\BeyondMedplusScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Medplus',
                'platform' => 'medplus',
                'country_id' => $nigeria->id,
                'search_url_template' => 'https://medplusnig.com/products?name={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\MedplusScraper',
                'requires_javascript' => true,
                'paginate_type' => '&',
                'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Essenza', 'platform' => 'essenza', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://www.essenza.ng/search?type=product&q={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\EssenzaScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'TOS Nigeria', 'platform' => 'tosnigeria', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://www.tosnigeria.com/?s={query}&type=product',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\TOSNigeriaScraper',
                'requires_javascript' => true, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Deoset', 'platform' => 'deoset', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://deoset.com/?s={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\DeosetScraper',
                'requires_javascript' => true, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Jiji Nigeria', 'platform' => 'jiji', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://jiji.ng/shop/{query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\JijiScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Purelife Pharmacy', 'platform' => 'purelife', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://www.purelifehealth.io/shop?search={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\PurelifeScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'HealthPlus Pharmacy', 'platform' => 'healthplus', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://healthplusnigeria.com/search?type=product&q={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\HealthplusScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'KunleAra Pharmacy', 'platform' => 'kunleara', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://kunlearapharmacy.ng/shop?query={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\KunleAraScraper',
                'requires_javascript' => true, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Mopheth Pharmacy', 'platform' => 'mopheth', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://mophethonline.com/?post_type=product&product-category=&s={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\MophethScraper',
                'requires_javascript' => false, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Asset Pharmacy', 'platform' => 'assetpharmacy', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://assetpharmacy.com/?s={query}&post_type=product&dgwt_wcas=1',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\AssetPharmacyScraper',
                'requires_javascript' => false, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Boluke Pharmacy', 'platform' => 'bolukepharmacy', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://bolukepharmacy.com/shop?query={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\BolukePharmacyScraper',
                'requires_javascript' => false, 'paginate_type' => null, 'paginate_keyword' => null,
            ],
            [
                'name' => 'MedicVille Pharmacy', 'platform' => 'medicvillepharmacy', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://medicvillepharmacy.com/products/?query={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\MedicVillePharmacyScraper',
                'requires_javascript' => false, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Hale & Hearty', 'platform' => 'haleandhearty', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://haleandhearty.ng/store/{query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\HaleAndHeartyScraper',
                'requires_javascript' => false, 'paginate_type' => '/', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'OneHealth', 'platform' => 'onehealth', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://onehealthng.com/category/health-and-wellness/{query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\OneHealthScraper',
                'requires_javascript' => false, 'paginate_type' => '?', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'Nett Pharmacy', 'platform' => 'nettpharmacy', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://nettpharmacy.com/search?q={query}&options%5Bprefix%5D=last&type=product',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\NettPharmacyScraper',
                'requires_javascript' => false, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
            [
                'name' => 'You-Buy', 'platform' => 'youbuy', 'country_id' => $nigeria->id,
                'search_url_template' => 'https://www.u-buy.com.ng/search/?q={query}',
                'scraper_class' => 'Src\Scraping\Infrastructure\Scrapers\YouBuyScraper',
                'requires_javascript' => false, 'paginate_type' => '&', 'paginate_keyword' => 'page',
            ],
        ];

        foreach ($stores as $storeData) {
            Store::updateOrCreate(
                ['name' => $storeData['name']],
                $storeData
            );
        }

        $this->command->info(count($stores).' scraper store configurations have been seeded.');
    }
}
