<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Content\Domain\Models\Conversation;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding conversation scenarios...');

        // --- SCENARIO 1: Blood Pressure Medication Confusion (The original, detailed one) ---
        $convo1 = Conversation::updateOrCreate(['name' => 'Blood Pressure Meds Demo'], ['is_active' => true]);
        $convo1->messages()->delete();
        $convo1->messages()->createMany([
            ['order' => 1, 'sender' => 'patient', 'text' => "Hi, I just got back from my doctor and I'm a bit confused.", 'delay_ms' => 2000],
            ['order' => 2, 'sender' => 'pharmacist', 'text' => "Hello! I'm here to help. What's on your mind?", 'delay_ms' => 3000],
            ['order' => 3, 'sender' => 'patient', 'text' => "I was prescribed Lisinopril, but I'm already taking Amlodipine for my blood pressure. Why do I need two?", 'delay_ms' => 4500],
            ['order' => 4, 'sender' => 'pharmacist', 'text' => "That's a great and very common question! Let me explain. Amlodipine works by relaxing your blood vessels...", 'delay_ms' => 4000],
            ['order' => 5, 'sender' => 'pharmacist', 'text' => '...while Lisinopril works in a completely different way, by preventing a hormone that tightens them. Using both together gives you better, safer control.', 'delay_ms' => 5000],
            ['order' => 6, 'sender' => 'patient', 'text' => 'Oh, that makes so much sense! Thank you for explaining that. I feel much better about it now.', 'delay_ms' => 4000],
            ['order' => 7, 'sender' => 'pharmacist', 'text' => "You're very welcome! Never hesitate to ask. That's what we're here for.", 'delay_ms' => 3000],
        ]);

        // --- SCENARIO 2: Diabetes Management (Short & Proactive) ---
        $convo2 = Conversation::updateOrCreate(['name' => 'Diabetes Follow-up Demo']);
        $convo2->messages()->delete();
        $convo2->messages()->createMany([
            ['order' => 1, 'sender' => 'pharmacist', 'text' => 'Hi Tunde, just checking in. How have your blood sugar readings been this week with the new Metformin dose?', 'delay_ms' => 2500],
            ['order' => 2, 'sender' => 'patient', 'text' => "They're a bit better, but I feel a little dizzy in the mornings.", 'delay_ms' => 3500],
            ['order' => 3, 'sender' => 'pharmacist', 'text' => "Thanks for letting me know. Let's talk about timing. Are you taking it with food? Sometimes taking it with breakfast instead of before can help with that.", 'delay_ms' => 4500],
            ['order' => 4, 'sender' => 'patient', 'text' => "I'll try that tomorrow. Thanks for the tip!", 'delay_ms' => 3000],
        ]);

        // --- SCENARIO 3: Asthma Inhaler Technique (Educational) ---
        $convo3 = Conversation::updateOrCreate(['name' => 'Asthma Inhaler Demo']);
        $convo3->messages()->delete();
        $convo3->messages()->createMany([
            ['order' => 1, 'sender' => 'patient', 'text' => "My son was prescribed a Ventolin inhaler, but I'm not sure if he's using it right.", 'delay_ms' => 2500],
            ['order' => 2, 'sender' => 'pharmacist', 'text' => "Getting the technique right is key! Can you tell me how he's using it?", 'delay_ms' => 3000],
            ['order' => 3, 'sender' => 'patient', 'text' => 'He just puts it in his mouth and presses the button while breathing in.', 'delay_ms' => 3500],
            ['order' => 4, 'sender' => 'pharmacist', 'text' => "Okay, that's a common starting point. For best results, he should breathe out fully first, then press the inhaler *just as he starts* to take a slow, deep breath in. Holding his breath for 10 seconds after is also very important.", 'delay_ms' => 6000],
            ['order' => 5, 'sender' => 'pharmacist', 'text' => "We can even schedule a quick video call to practice together if you'd like!", 'delay_ms' => 3000],
        ]);

        // --- SCENARIO 4: Antibiotic Side Effects (Quick & Reassuring) ---
        $convo4 = Conversation::updateOrCreate(['name' => 'Antibiotic Side Effects Demo']);
        $convo4->messages()->delete();
        $convo4->messages()->createMany([
            ['order' => 1, 'sender' => 'patient', 'text' => 'I started taking Augmentin yesterday and my stomach feels upset.', 'delay_ms' => 2500],
            ['order' => 2, 'sender' => 'pharmacist', 'text' => "That's a very common and usually mild side effect of Augmentin. Are you taking it with a meal?", 'delay_ms' => 3500],
            ['order' => 3, 'sender' => 'patient', 'text' => 'No, I took it on an empty stomach.', 'delay_ms' => 2000],
            ['order' => 4, 'sender' => 'pharmacist', 'text' => 'Try taking your next dose with food. That almost always solves the issue. If it continues or gets worse, please let me know right away.', 'delay_ms' => 4500],
        ]);

        // --- SCENARIO 5: Cost Savings (Commercial & Value-driven) ---
        $convo5 = Conversation::updateOrCreate(['name' => 'Cost Savings Demo']);
        $convo5->messages()->delete();
        $convo5->messages()->createMany([
            ['order' => 1, 'sender' => 'pharmacist', 'text' => "Hi Mrs. Ade, I'm reviewing your file and see you're on Lipitor. I just wanted to let you know a high-quality generic version, Atorvastatin, is available.", 'delay_ms' => 3000],
            ['order' => 2, 'sender' => 'patient', 'text' => 'Oh really? Is it the same thing? Will it work as well?', 'delay_ms' => 3000],
            ['order' => 3, 'sender' => 'pharmacist', 'text' => "Yes, it has the exact same active ingredient and is approved by NAFDAC. It's just as effective but could save you over ₦15,000 a month. Shall I discuss this option with your doctor?", 'delay_ms' => 5000],
            ['order' => 4, 'sender' => 'patient', 'text' => 'Wow, yes please! That would be amazing.', 'delay_ms' => 2500],
        ]);

        // --- SCENARIO 6: Simple Refill Request (Convenience) ---
        $convo6 = Conversation::updateOrCreate(['name' => 'Simple Refill Demo']);
        $convo6->messages()->delete();
        $convo6->messages()->createMany([
            ['order' => 1, 'sender' => 'patient', 'text' => 'Hi, I need a refill for my Vitamin D.', 'delay_ms' => 2000],
            ['order' => 2, 'sender' => 'pharmacist', 'text' => "No problem at all, Aisha. I see you have one refill left. I'll get it ready for you at Mopheth Pharmacy.", 'delay_ms' => 3500],
            ['order' => 3, 'sender' => 'pharmacist', 'text' => "It will be ready for pickup this afternoon. I'll send you another message the moment it's bagged and waiting.", 'delay_ms' => 3000],
            ['order' => 4, 'sender' => 'patient', 'text' => 'Perfect, thank you!', 'delay_ms' => 2000],
        ]);

        $this->command->info('Seeded 6 conversation scenarios.');
    }
}
