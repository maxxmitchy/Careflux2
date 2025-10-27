<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Src\Location\Domain\Models\City;
use Src\Location\Domain\Models\Country;
use Src\Location\Domain\Models\State;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Seeding Country, States, and Cities for Nigeria...');

            // 1. Create the Country: Nigeria
            $nigeria = Country::updateOrCreate(
                ['short_code' => 'NG'],
                ['name' => 'Nigeria']
            );

            // 2. Define and create all States
            $statesData = [
                'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River',
                'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano',
                'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun',
                'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara', 'FCT - Abuja',
            ];

            foreach ($statesData as $stateName) {
                State::updateOrCreate(
                    ['country_id' => $nigeria->id, 'name' => $stateName]
                );
            }
            $this->command->comment(count($statesData).' states created or verified.');

            // 3. Define Cities for each State
            $citiesData = [
                'Lagos' => [
                    'Ikeja', 'Lekki', 'Victoria Island', 'Ikoyi', 'Surulere', 'Apapa', 'Lagos Island', 'Yaba',
                    'Festac Town', 'Ajah', 'Badagry', 'Epe', 'Ikorodu', 'Agege', 'Alimosho', 'Amuwo-Odofin',
                    'Eti-Osa', 'Ifako-Ijaiye', 'Kosofe', 'Lagos Mainland', 'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu',
                ],
                'FCT - Abuja' => ['Abuja', 'Garki', 'Wuse', 'Maitama', 'Asokoro', 'Gwarinpa', 'Kubwa'],
                'Rivers' => ['Port Harcourt', 'Bonny', 'Okrika', 'Eleme'],
                'Oyo' => ['Ibadan', 'Ogbomoso', 'Oyo', 'Iseyin'],
                'Kano' => ['Kano', 'Dawakin Kudu', 'Wudil'],
                'Kaduna' => ['Kaduna', 'Zaria', 'Kafanchan'],
                'Abia' => ['Umuahia', 'Aba'],
                'Adamawa' => ['Yola', 'Mubi'],
                'Akwa Ibom' => ['Uyo', 'Eket', 'Ikot Ekpene'],
                'Anambra' => ['Awka', 'Onitsha', 'Nnewi'],
                'Bauchi' => ['Bauchi'],
                'Bayelsa' => ['Yenagoa'],
                'Benue' => ['Makurdi'],
                'Borno' => ['Maiduguri'],
                'Cross River' => ['Calabar', 'Ikom'],
                'Delta' => ['Asaba', 'Warri', 'Sapele'],
                'Ebonyi' => ['Abakaliki'],
                'Edo' => ['Benin City', 'Auchi'],
                'Ekiti' => ['Ado-Ekiti'],
                'Enugu' => ['Enugu', 'Nsukka'],
                'Gombe' => ['Gombe'],
                'Imo' => ['Owerri', 'Orlu'],
                'Jigawa' => ['Dutse'],
                'Katsina' => ['Katsina', 'Funtua'],
                'Kebbi' => ['Birnin Kebbi'],
                'Kogi' => ['Lokoja', 'Okene'],
                'Kwara' => ['Ilorin', 'Offa'],
                'Nasarawa' => ['Lafia'],
                'Niger' => ['Minna', 'Suleja'],
                'Ogun' => ['Abeokuta', 'Ijebu-Ode', 'Sango Ota'],
                'Ondo' => ['Akure', 'Ondo Town', 'Owo'],
                'Osun' => ['Osogbo', 'Ile-Ife'],
                'Plateau' => ['Jos', 'Bukuru'],
                'Sokoto' => ['Sokoto'],
                'Taraba' => ['Jalingo'],
                'Yobe' => ['Damaturu'],
                'Zamfara' => ['Gusau'],
            ];

            // 4. Get all states just created, keyed by name for efficient lookup.
            $stateModels = State::where('country_id', $nigeria->id)->get()->keyBy('name');
            $cityCount = 0;

            // 5. Create Cities
            foreach ($citiesData as $stateName => $cities) {
                if (! $stateModels->has($stateName)) {
                    $this->command->warn("State '{$stateName}' not found in the database. Skipping its cities.");

                    continue;
                }

                $stateId = $stateModels[$stateName]->id;

                foreach ($cities as $cityName) {
                    City::updateOrCreate(
                        ['state_id' => $stateId, 'name' => $cityName]
                    );
                    $cityCount++;
                }
            }
            $this->command->comment($cityCount.' cities created or verified.');
        });

        $this->command->info('Location seeding for Nigeria completed successfully.');
    }
}
