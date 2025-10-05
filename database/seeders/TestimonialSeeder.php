<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default testimonials...');

        // NOTE: For images to work, create a `public/storage/testimonials` directory
        // and place correspondingly named images inside (e.g., 'chiamaka.jpg').
        $testimonials = [
            [
                'author_name' => 'Chiamaka Nwosu',
                'author_location' => 'Lekki, Lagos',
                'quote' => 'For the first time, a pharmacist actually called to check how I was feeling after starting my medication. It felt like I truly had a health partner. Game-changing service!',
                'author_image' => 'testimonials/chiamaka.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Adebayo Johnson',
                'author_location' => 'Ikeja, Lagos',
                'quote' => 'No more running from pharmacy to pharmacy. I used the search, found my medication in stock nearby, and had it delivered the same day. Incredibly efficient.',
                'author_image' => 'testimonials/adebayo.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Ngozi Chukwuma',
                'author_location' => 'Enugu, Enugu State',
                'quote' => 'I used to forget my blood pressure medication all the time. Now with the Careflux reminders and sachets, I have zero excuses. My doctor has noticed the difference too!',
                'author_image' => 'testimonials/ngozi.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Babatunde Ajayi',
                'author_location' => 'Yaba, Lagos',
                'quote' => 'The Careflux delivery rider was at my door less than two hours after I placed my order. That kind of speed and reliability is unmatched in Nigeria.',
                'author_image' => 'testimonials/babatunde.jpg',
                'rating' => 4,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Oluwatoyin Martins',
                'author_location' => 'Ibadan, Oyo State',
                'quote' => 'I didn’t realize how much stress I was carrying managing my father’s multiple prescriptions until Careflux took over. They made it simple, safe, and reassuring.',
                'author_image' => 'testimonials/oluwatoyin.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Chidera Umeh',
                'author_location' => 'Awka, Anambra State',
                'quote' => 'Finally, a healthcare app that feels designed for Nigerians. No hidden charges, no stress, just genuine care and follow-up.',
                'author_image' => 'testimonials/chidera.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Grace Ekanem',
                'author_location' => 'Calabar, Cross River',
                'quote' => 'As a busy professional, I never had time to track my medications properly. Careflux sachets have made adherence foolproof. I wish this existed years ago.',
                'author_image' => 'testimonials/grace.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Ibrahim Musa',
                'author_location' => 'Kaduna, Kaduna State',
                'quote' => 'What impressed me most was the pharmacist actually calling to explain my antibiotics. Nobody has ever done that before. This is next-level healthcare.',
                'author_image' => 'testimonials/ibrahim.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Amaka Ofor',
                'author_location' => 'Owerri, Imo State',
                'quote' => 'I used Careflux during Ramadan and the pharmacist guided me on how to adjust my medications for fasting. That kind of support is priceless.',
                'author_image' => 'testimonials/amaka.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Samuel Adeyemi',
                'author_location' => 'Ilorin, Kwara State',
                'quote' => 'I was skeptical at first, but after two months, I can confidently say Careflux is the best decision I’ve made for my health. They combine tech with a human touch.',
                'author_image' => 'testimonials/samuel.jpg',
                'rating' => 4,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Zainab Lawal',
                'author_location' => 'Kano, Kano State',
                'quote' => 'Getting my insulin on time has always been stressful. Careflux removed that stress completely. My sugar levels are more stable than ever.',
                'author_image' => 'testimonials/zainab.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Kelvin George',
                'author_location' => 'Port Harcourt, Rivers State',
                'quote' => 'I love that Careflux feels personal. I don’t feel like just another customer — they know my prescriptions, they check in, and they care.',
                'author_image' => 'testimonials/kelvin.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],

            [
                'author_name' => 'Funke Adewale',
                'author_location' => 'Garki, Abuja',
                'quote' => 'My personal pharmacist, Sarah, helped me understand why my doctor prescribed two different blood pressure meds. Her explanation was clearer than anything I found online. I feel so much more confident in my treatment now.',
                'author_image' => 'testimonials/funke.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Emeka Okafor',
                'author_location' => 'Wuse II, Abuja',
                'quote' => 'Managing my diabetes has become so much less stressful with Careflux. The automatic refill reminders mean I never have to worry about running out of my metformin.',
                'author_image' => 'testimonials/emeka.jpg',
                'rating' => 4,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Hajiya Aisha Bello',
                'author_location' => 'Victoria Island, Lagos',
                'quote' => 'I use Careflux to manage my elderly mother\'s prescriptions. Knowing a professional is keeping an eye on her refills and well-being gives me incredible peace of mind.',
                'author_image' => 'testimonials/aisha.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'author_name' => 'Tunde Alabi',
                'author_location' => 'Surulere, Lagos',
                'quote' => 'The platform is straightforward, and the follow-up service is something I never knew I needed. It\'s clear they actually care about your health, not just making a sale.',
                'author_image' => 'testimonials/tunde.jpg',
                'rating' => 5,
                'is_featured' => true,
            ],
        ];

        foreach ($testimonials as $testimonialData) {
            Testimonial::updateOrCreate(
                ['author_name' => $testimonialData['author_name']], // Use a unique key to prevent duplicates
                $testimonialData
            );
        }
    }
}
