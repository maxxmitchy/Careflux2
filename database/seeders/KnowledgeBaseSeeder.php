<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding comprehensive Knowledge Base for Pharmacist Playbook...');

        // Reset tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        KnowledgeBaseArticle::truncate();
        KnowledgeBaseCategory::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 1: Patient Onboarding & Welcome
        |--------------------------------------------------------------------------
        */
        $cat1 = KnowledgeBaseCategory::create([
            'name' => 'Patient Onboarding',
            'description' => 'Messages for welcoming new patients, explaining the pharmacist’s role, and early engagement.',
            'sort_order' => 10,
        ]);

        $cat1->articles()->createMany([
            [
                'title' => 'Initial Welcome Message (WhatsApp)',
                'content' => "Hello {patient_name}, this is {pharmacist_name}, your personal pharmacist from Careflux! 👋\n\nI'm so glad to have you on board. I'll be your main point of contact for any medication questions, refill reminders, and follow-ups.\n\nMy goal is to make sure your health journey is smooth and supported. Is there anything on your mind I can help with today?",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => '48-Hour Onboarding Check-in',
                'content' => "Hi {patient_name}, just a quick check-in from your Careflux pharmacist. How are you settling in? I just wanted to remind you that I'm here if you have any questions at all. Don't hesitate to reach out!",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Explaining the Pharmacist’s Role',
                'content' => "Hello {patient_name}! Just to clarify my role, think of me as your health partner. Beyond just dispensing, I'm here to help you understand your medications, manage side effects, and work with you to achieve your health goals. It's proactive care, and it's all part of the Careflux service.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Consent & Privacy Confirmation',
                'content' => 'Hello {patient_name}, before we proceed, please confirm you’re happy for us to keep and use your medical details to support your care. Your information is private — reply YES to consent or ASK to learn more about our privacy policy.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication History Request',
                'content' => 'Hi {patient_name}, could you share a list of any medications, vitamins or supplements you are currently taking? This helps me check for interactions and keep your treatment safe.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Allergy & Reaction Check',
                'content' => 'Hello {patient_name}, do you have any known drug allergies or past reactions (e.g., rash, breathing trouble)? Please reply with the allergy and reaction so I can flag it in your record.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Communication Preferences',
                'content' => 'Hi {patient_name}, what’s the best way to reach you? (WhatsApp, SMS, Call). Also tell me times that work best for medication reminders or counseling.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Emergency Contact Request',
                'content' => 'Hello {patient_name}, could you provide an emergency contact name and number we can reach if there is a medication-related issue? This stays private and is used only for urgent situations.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'How We Deliver Care (Quick Guide)',
                'content' => 'Hi {patient_name}, here’s how we support you: refill reminders, side-effect checks, delivery coordination, and quick answers to med questions. If you want a printed copy of services, reply PRINT.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Preparing for Your First Review',
                'content' => 'Hello {patient_name}, for our first medication review, please have a list of medicines, last lab results (if any), and notes about how you feel. I’ll walk through everything with you.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Onboarding: Delivery & Handling Preferences',
                'content' => 'Hi {patient_name}, do you prefer deliveries to home or to pick-up at {pharmacy_name}? Also tell us any special handling instructions (e.g., leave with neighbour).',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Welcome Packet — Key Contacts & Hours',
                'content' => 'Hello {patient_name}, welcome to Careflux. Here are key contacts and operating hours: pharmacist: {pharmacist_name}, deliveries: {delivery_hours}, urgent line: {urgent_number}. Reply CONTACTS to receive these as a summary.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Care Plan Overview (Short)',
                'content' => 'Hi {patient_name}, a brief overview of your care plan: main goals, which meds we’ll monitor, and our follow-up cadence. Reply OVERVIEW if you’d like a printable copy.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Introduce Your Care Team',
                'content' => 'Hello {patient_name}, your Careflux team includes: {pharmacist_name} (pharmacist), {tech_name} (pharmacy tech) and our delivery partner. Reply TEAM for direct contact options or to add a caregiver.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'How to Read Your Medication Label (Quick Guide)',
                'content' => 'Hi {patient_name}, quick label tips: dosage = how much, frequency = how often, PRN = as needed. If a label is unclear, send a photo and I’ll explain it for you.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Pre-Consultation Tech Check (for video calls)',
                'content' => 'Hello {patient_name}, if you booked a video consult please check: camera works, good internet, quiet room. Reply READY if you’re set or NEED HELP for support.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Assistive Packaging & Special Requests',
                'content' => 'Hi {patient_name}, do you need large-print labels, blister packs, or easy-open packaging? Reply PACK with preferences (e.g., LARGE_FONT, BLISTER) and we’ll arrange it.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Prescription Transfer Authorization (Quick)',
                'content' => 'Hello {patient_name}, to transfer a prescription here please reply TRANSFER and include original pharmacy name and prescriber. We’ll handle the rest after your confirmation.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Confirm Preferred Pharmacy Location',
                'content' => 'Hi {patient_name}, which pickup location suits you best? Reply with branch name (e.g., {pharmacy_branch_1}) or reply DELIVERY for home delivery as default.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication Safety Callback Offer',
                'content' => 'Hello {patient_name}, if you’d like a short follow-up call after your first week on a new med, reply CALLBACK and suggest a day/time and we’ll schedule it.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Welcome Survey — Your Top Health Priorities',
                'content' => 'Hi {patient_name}, quick one: what’s your top health priority right now? Reply one word: BLOOD_PRESSURE, DIABETES, PAIN, SKIN, OTHER. This helps tailor our support.',
                'target_audience' => 'pharmacist',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 2: Medication Follow-ups
        |--------------------------------------------------------------------------
        */
        $cat2 = KnowledgeBaseCategory::create([
            'name' => 'Medication Follow-ups',
            'description' => 'Templates for checking on patients after they start new medications or experience side effects.',
            'sort_order' => 20,
        ]);

        $cat2->articles()->createMany([
            [
                'title' => 'New Prescription - 48-Hour Check-in',
                'content' => "Hi {patient_name}, it's {pharmacist_name} from Careflux. I'm just following up on the new medication, {medication_name}, you started two days ago. How are you feeling with it so far? Any questions or concerns?",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'New Prescription - 7-Day Check-in',
                'content' => 'Hello {patient_name}, checking in one week after you started {medication_name}. By now, your body is getting used to it. Have you noticed any improvements or any side effects we should talk about?',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Follow-up for Side Effects (Empathetic)',
                'content' => "Hi {patient_name}, thank you for letting me know you're experiencing {side_effect}. That can be uncomfortable, but it's often a sign the medication is starting to work. Let's discuss a few simple ways to manage it. Do you have a moment to chat?",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Chronic Medication - 1-Month Check-in',
                'content' => "Hi {patient_name}, it's been a month since you started your {medication_name}. This is a great time to review your progress. Have you been able to check your blood pressure/sugar levels recently? Let's see how well it's working for you.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Missed Dose — Check-in',
                'content' => 'Hi {patient_name}, I noticed a missed refill/ dose for {medication_name}. Are you okay? If you missed a dose, tell me when — I can advise next steps or arrange a refill.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => '7-Day Symptom Diary Prompt',
                'content' => 'Hello {patient_name}, to track how {medication_name} is working, could you record simple notes for 7 days (symptoms, side effects, sleep)? I can review them with you.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Titration / Dose Adjustment Check',
                'content' => 'Hi {patient_name}, you were started on {medication_name} with a dose plan. Have you noticed any issues since your last change? If yes, reply and we’ll review whether a change is needed.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Polypharmacy Safety Prompt',
                'content' => 'Hello {patient_name}, because you’re taking several medicines, please send an updated list so I can check for interactions and simplify where possible.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'OTC & Supplement Check',
                'content' => 'Hi {patient_name}, some over-the-counter meds and supplements can interact with prescriptions. Please tell me anything new you’ve started taking (e.g., vitamins, herbs).',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Adherence Barrier Check',
                'content' => 'Hello {patient_name}, I want to understand any challenges you have taking {medication_name} (cost, side effects, forgetfulness). Reply with the main issue and I’ll suggest solutions.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication Effectiveness Quick Survey',
                'content' => 'Hi {patient_name}, on a scale of 1–5 how would you rate the effect of {medication_name} so far? 1 = no benefit, 5 = excellent. Your reply helps guide next steps.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'When to Seek Immediate Care (Safety Check)',
                'content' => "Hello {patient_name}, if you experience severe symptoms like difficulty breathing, swelling of face/throat, or fainting after taking {medication_name}, call emergency services right away and reply 'EMERGENCY' so we can escalate.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Lab Results Review Request',
                'content' => 'Hi {patient_name}, have you had any recent lab checks (BP readings, blood sugar, kidney tests)? Reply with date(s) and results or send a photo so I can review medication effects.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Pregnancy & Breastfeeding Safety Check',
                'content' => 'Hello {patient_name}, are you pregnant, planning pregnancy, or breastfeeding? Some medicines need review for safety—please let me know so I can check yours.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Renal / Liver Function Screening Prompt',
                'content' => 'Hi {patient_name}, do you have known kidney or liver issues or recent tests? Certain drugs require dose review—reply YES if this applies or send your latest test date.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Therapeutic Goal Check-in',
                'content' => 'Hello {patient_name}, what outcome are you aiming for with {medication_name}? (e.g., lower BP, less pain, better sleep). Reply with your goal so we can track progress together.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication Restart Guidance',
                'content' => 'Hi {patient_name}, if you stopped {medication_name} and are thinking of restarting, tell me how long you paused and why—I’ll advise on safe next steps and monitoring.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Infection / Immunosuppression Check',
                'content' => 'Hello {patient_name}, if you are on immunosuppressants or have recent infections, notify us. We can check interactions and advise on additional monitoring or precautions.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Drug Interaction Advisory — Please Upload List',
                'content' => 'Hi {patient_name}, to proactively check for interactions, please reply with a list of any new OTC meds, supplements or herbs you’ve taken in the last 2 weeks.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Monitoring Schedule Confirmation',
                'content' => 'Hello {patient_name}, please confirm how often you can share readings (BP, glucose): DAILY, WEEKLY, or MONTHLY. This helps us schedule follow-ups appropriately.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Specialist Liaison Offer',
                'content' => 'Hi {patient_name}, if your symptoms need specialist input we can contact your prescriber or refer—reply REFER and include any preferred clinician contact details.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Home Monitoring Device Guidance',
                'content' => 'Hello {patient_name}, if you use a BP cuff or glucometer, I can help validate readings and show correct technique. Reply DEVICE and include device model if available.',
                'target_audience' => 'pharmacist',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 3: Refill Reminders
        |--------------------------------------------------------------------------
        */
        $cat3 = KnowledgeBaseCategory::create([
            'name' => 'Refill Reminders',
            'description' => 'Proactive messages to ensure patients never run out of their essential medications.',
            'sort_order' => 30,
        ]);

        $cat3->articles()->createMany([
            [
                'title' => '7-Day Refill Reminder',
                'content' => "Hi {patient_name}, a friendly reminder from your Careflux pharmacist! Your supply of {medication_name} will be due for a refill in about a week.\n\nShall I get the process started for you to ensure it's ready for delivery with no stress?",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => '2-Day “Urgent” Refill Reminder',
                'content' => "Hello {patient_name}, just a quick alert. Your {medication_name} is running low and is due for a refill in the next 48 hours. To avoid missing a dose, please let me know if you'd like me to process your refill now.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Refill Confirmed Message',
                'content' => "Great! I've processed your refill for {medication_name}. It will be delivered from {pharmacy_name} on or before {delivery_date}. I'll let you know once it's on its way.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => '30-Day Preemptive Refill Reminder',
                'content' => 'Hi {patient_name}, you have about 30 days of {medication_name} left. Would you like me to schedule a refill now so it arrives on time?',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Auto-Refill Enrollment Offer',
                'content' => 'Hello {patient_name}, we can auto-enroll your {medication_name} so refills are processed automatically. Reply AUTO to opt in or ASK to learn how it works.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Cost / Insurance Assistance Offer',
                'content' => 'Hi {patient_name}, if cost or insurance is a concern for your {medication_name}, reply HELP and I’ll check cheaper options or support programs for you.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Generic Substitution Option',
                'content' => 'Hello {patient_name}, a generic equivalent of {medication_name} may be available and could reduce cost. Would you like me to check availability and savings for you?',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Partial Refill / Emergency Supply Offer',
                'content' => 'Hi {patient_name}, if you’re short on medication right now, I can arrange a partial supply to keep you covered until the full refill is available. Reply PARTIAL to proceed.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Refill Delay — Apology & ETA',
                'content' => 'Hello {patient_name}, we’re sorry: there’s a short delay for {medication_name}. Expected availability: {estimated_date}. Would you like an alternative or to be notified when it arrives?',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Refill Ready for In-Store Pickup',
                'content' => 'Hi {patient_name}, your refill for {medication_name} is ready at {pharmacy_name}. Reply PICKUP for opening times or DELIVER to arrange courier.',
                'target_audience' => 'pharmacist',
            ], [
                'title' => 'Refill Sync with Clinic Visits',
                'content' => 'Hi {patient_name}, we can align your medication refills to your clinic appointments to reduce trips. Reply SYNC_APPT with your next appointment date and we’ll arrange it.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Prescriber Unavailable — Interim Supply Offer',
                'content' => 'Hello {patient_name}, if your prescriber is unavailable and you are short on critical meds, we can often arrange a short interim supply—reply INTERIM and I’ll check eligibility.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Out-of-Country Travel / Export Documentation',
                'content' => 'Hi {patient_name}, travelling abroad soon? Reply TRAVEL with destination and dates and we’ll check export documentation and sufficient supply for your trip.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Pause Auto-Refill (Temporary Hold)',
                'content' => 'Hello {patient_name}, want to temporarily pause auto-refills? Reply PAUSE with the expected resume date and we’ll hold processing until then.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Cancel Subscription / Refill Plan',
                'content' => 'Hi {patient_name}, to cancel a scheduled refill plan reply CANCEL with the medication name. We’ll confirm before stopping any automatic processing.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Alternate Pickup Location Offer',
                'content' => 'Hello {patient_name}, if you’re near another branch we can have your refill ready there. Reply ALT_PICKUP and name a branch to check availability.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Eco-Packaging Option for Refills',
                'content' => 'Hi {patient_name}, would you prefer reduced packaging for refills (fewer bags, consolidated boxes)? Reply ECO to opt-in for greener packaging options.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Sharps & Disposal Collection Offer',
                'content' => 'Hello {patient_name}, if you use sharps (pens/needles), we offer safe disposal pickup. Reply SHARPS and we’ll arrange a collection with guidance.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Notify Me When Price Changes',
                'content' => 'Hi {patient_name}, want an alert if the price for {medication_name} changes before your refill? Reply PRICE_NOTIFY to receive updates before we process your order.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Bulk Refill for Family Members',
                'content' => 'Hello {patient_name}, need multiple family members’ refills at once? Reply FAMILY with names and meds and we’ll consolidate to a single delivery where possible.',
                'target_audience' => 'pharmacist',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 4: Health & Wellness Education
        |--------------------------------------------------------------------------
        */
        $cat4 = KnowledgeBaseCategory::create([
            'name' => 'Health Education & Tips',
            'description' => 'Templates for sharing health education, preventive care, and general wellness advice.',
            'sort_order' => 40,
        ]);

        $cat4->articles()->createMany([
            [
                'title' => 'Blood Pressure Management Tip',
                'content' => "Hi {patient_name}, a quick tip for managing your blood pressure: try to reduce your salt intake this week. Swapping salty snacks for fruits can make a big difference! Let me know if you'd like more simple tips like this.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Importance of Adherence',
                'content' => "Hello {patient_name}, just wanted to share a thought. Taking your medication consistently, even when you feel good, is the most powerful thing you can do for your long-term health. You're doing a great job, keep it up!",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Hydration Reminder',
                'content' => 'Hi {patient_name}, a quick wellness reminder from your Careflux pharmacist: remember to stay hydrated today! Drinking enough water is essential for your medications to work effectively and for your overall health. Cheers to your health! 💧',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Diabetes Management Basics',
                'content' => 'Hi {patient_name}, small daily steps help: monitor blood sugar as advised, be consistent with medication, and favour low-GI foods. If you want a simple meal list, reply DIET.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Safe OTC Painkiller Use',
                'content' => 'Hello {patient_name}, use OTC painkillers only as directed. Don’t mix multiple products containing the same active ingredient (e.g., paracetamol). Ask me if you’re unsure which one to take.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Vaccination Opportunities',
                'content' => 'Hi {patient_name}, immunizations (like seasonal flu) may reduce complications. If you’d like, I can check which vaccines you’re due for and arrange it at {pharmacy_name}.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Smoking Cessation Quick Tip',
                'content' => 'Hello {patient_name}, small changes—like delaying the first cigarette by 1 hour—can help reduce cravings. If you want support or nicotine replacement options, reply QUIT and I’ll help plan next steps.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Dietary Fiber & Gut Health',
                'content' => 'Hi {patient_name}, adding fiber slowly (fruits, vegetables, whole grains) helps digestion and medication tolerance. Increase water intake as you add fiber to avoid constipation.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication Storage & Safe Disposal',
                'content' => 'Hello {patient_name}, store medicines in a cool, dry place away from children. For expired or unused meds, return them to {pharmacy_name} for safe disposal.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Exercise & Medication Timing',
                'content' => 'Hi {patient_name}, light to moderate exercise often helps recovery—just check with me if your medication affects heart rate or blood sugar so we can time exercise safely.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Alcohol Interaction Reminder',
                'content' => 'Hello {patient_name}, some medicines interact with alcohol. If you drink alcohol regularly, tell me which meds you take so I can advise on safe limits or alternatives.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Antibiotic Stewardship — Finish the Course',
                'content' => 'Hi {patient_name}, if you were prescribed antibiotics, finish the full course even if you feel better to reduce risk of resistance. If you have side effects, tell me.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Inhaler Technique & Spacer Use Guide',
                'content' => 'Hello {patient_name}, proper inhaler technique and using a spacer improves effectiveness. Reply INHALER and I’ll send a short how-to guide for your device.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Allergic Reaction vs Expected Side Effect',
                'content' => 'Hi {patient_name}, expected side effects are usually predictable and mild; allergic reactions include hives, swelling, or breathing difficulty. If you see these, seek urgent care and reply EMERGENCY.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Falls Prevention for Dizziness-Causing Meds',
                'content' => 'Hello {patient_name}, if your medicines cause dizziness: stand slowly, keep rooms well-lit and remove trip hazards. Reply CHECK and I’ll send a home-safety checklist.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Bone Health — Nutrition & Lifestyle Tips',
                'content' => 'Hi {patient_name}, to support bone health include calcium sources and weight-bearing exercise. If you take bone medications, I can send a short meal & exercise guide.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Medication Timing with Meals',
                'content' => 'Hello {patient_name}, some meds should be taken with food, others on an empty stomach. If you’re unsure about any listed medicines, reply MED_TIMING and I’ll clarify each one.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Sleep Hygiene & Medicines That Affect Sleep',
                'content' => 'Hi {patient_name}, certain meds can affect sleep. Good sleep habits (regular schedule, low screens before bed) help. Reply SLEEP and I’ll suggest practical tips.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Herbal Remedies — What to Watch For',
                'content' => 'Hello {patient_name}, many herbal remedies interact with drugs. If you use herbal products, reply HERBAL and list them so I can check for interactions.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Recognizing Early Signs of Dehydration',
                'content' => 'Hi {patient_name}, early signs include dizziness, low urine output and dry mouth—important if you’re on diuretics or certain blood pressure meds. Drink fluids and tell me your meds.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Opioid Safety & Naloxone Offer',
                'content' => 'Hello {patient_name}, if you’re on opioid pain relief, we can discuss safety, storage, and naloxone (overdose reversal). Reply SAFETY to learn more or request naloxone details.',
                'target_audience' => 'pharmacist',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 5: Patient Engagement & Re-engagement
        |--------------------------------------------------------------------------
        */
        $cat5 = KnowledgeBaseCategory::create([
            'name' => 'Patient Engagement',
            'description' => 'Templates for maintaining communication, celebrating progress, and requesting feedback.',
            'sort_order' => 50,
        ]);

        $cat5->articles()->createMany([
            [
                'title' => 'Re-engagement (After a Week of Silence)',
                'content' => "Hi {patient_name}, just checking in – haven't heard from you in a little while and wanted to make sure everything is okay. If you have any questions or need anything, please don't hesitate to reach out.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Celebrating a Health Milestone',
                'content' => "Hello {patient_name}, I was just reviewing your record and saw that your blood pressure readings have been consistently in the healthy range for a month now. That's fantastic progress! Your commitment is really paying off. Keep up the great work!",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Requesting Feedback',
                'content' => "Hi {patient_name}, your feedback is incredibly valuable for us to improve. If you have a moment, could you share how your experience with Careflux has been so far? We're always looking to serve you better.",
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Birthday / Wellness Anniversary Note',
                'content' => 'Hi {patient_name}, happy birthday! 🎉 A gentle health tip for the year ahead: keep taking {medication_name} as prescribed. If you’d like a quick check-up this month, reply BOOK.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Seasonal Wellness Tip Campaign',
                'content' => 'Hello {patient_name}, seasonal tip: in hot weather, stay hydrated and keep medicines that need refrigeration cool. Reply TIPS for a short seasonal checklist.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Refer-a-Friend Information',
                'content' => 'Hi {patient_name}, if a friend would benefit from Careflux, refer them and we’ll thank you with a small service credit. Reply REFER for details and terms.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Share Your Story (Opt-In)',
                'content' => 'Hello {patient_name}, would you be willing to share a short success story about your care? Reply YES to consent and we’ll follow up for details—your identity stays private unless you say otherwise.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Monthly Health Checklist Offer',
                'content' => 'Hi {patient_name}, want a simple monthly checklist (med refills, BP check, hydration)? Reply CHECKLIST and I’ll send one you can save.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Invite: Short Educational Webinar',
                'content' => 'Hello {patient_name}, we’re running a 20-minute webinar on heart health next week. Reply JOIN to reserve a place—spaces limited.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Update Your Contact Details',
                'content' => 'Hi {patient_name}, if any of your contact or delivery details have changed, please reply UPDATE so we can keep your care seamless.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => '1-Question Satisfaction Pulse',
                'content' => 'Hi {patient_name}, quick check: How satisfied are you with our service today? Reply 1 (low) to 5 (high). Thanks — your feedback helps us improve.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Book a 30-Minute Medication Review',
                'content' => 'Hello {patient_name}, want a detailed 30-minute medication review to optimise your regimen? Reply BOOK_REVIEW and suggest times that work for you.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Weekly Wellness Tips — Opt In',
                'content' => 'Hi {patient_name}, reply WEEKLY to receive short weekly wellness tips tailored to your conditions (e.g., BP checks, diet tips, medication reminders).',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Set Up Medication Reminders (Assisted)',
                'content' => 'Hello {patient_name}, need help setting phone alarms or an app for reminders? Reply SET_REMIND and a team member will guide you step-by-step by call or message.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Adherence Rewards Program — Invite',
                'content' => 'Hi {patient_name}, stay on-time with refills and earn small credits toward service fees. Reply JOIN_REWARDS to opt in and learn how it works.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Share Care Summary with Your GP (Auth)',
                'content' => 'Hello {patient_name}, we can share a concise medication summary with your GP with your approval. Reply SHARE and include your GP’s name to authorize sharing.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Short Guide: Using Pill Organizers',
                'content' => 'Hi {patient_name}, a pillbox can simplify taking doses. Reply PILLBOX and I will send a short guide plus tips for weekly setup.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Consent for Receiving Medical Images',
                'content' => 'Hello {patient_name}, occasionally we request photos (medicine labels, rashes). Do you consent to sending images for clinical review? Reply YES or NO.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Careflux App Invite (Benefits Summary)',
                'content' => 'Hi {patient_name}, the Careflux app helps you track meds and deliveries. Reply APP and I’ll send an invite and short benefits summary to help you decide.',
                'target_audience' => 'pharmacist',
            ],
            [
                'title' => 'Annual Medication Review — Reminder & Booking',
                'content' => 'Hello {patient_name}, it’s time for your annual medication review to ensure safety and effectiveness. Reply ANNUAL to book a convenient time.',
                'target_audience' => 'pharmacist',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CATEGORY 6: Technician Scripts
        |--------------------------------------------------------------------------
        */
        $cat6 = KnowledgeBaseCategory::create([
            'name' => 'Technician Scripts',
            'description' => 'Message templates and internal communication scripts for pharmacy technicians.',
            'sort_order' => 60,
        ]);

        $cat6->articles()->createMany([
            [
                'title' => 'Order Packed & Ready Confirmation',
                'content' => 'Hello {patient_name}, great news! Your order #{order_number} from {pharmacy_name} has been packed and is now ready for dispatch. Our delivery partner will contact you shortly.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Stock Unavailability Alert (Internal)',
                'content' => 'Hi {pharmacist_name}, please be advised that {product_name} for order #{order_number} is currently out of stock. Please contact the patient to discuss alternatives.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Out for Delivery Notification',
                'content' => 'Your Careflux order #{order_number} is out for delivery and should arrive today. Please ensure someone is available to receive it.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Price Verification Request (Internal)',
                'content' => 'Hi {pharmacist_name}, a task has been assigned to you to verify the current price of {product_name}. Please update it in the inventory system.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Payment Confirmed — Next Steps',
                'content' => 'Hi {pharmacist_name}, payment for order #{order_number} has been received. Please proceed to pack and schedule for dispatch. Notify patient with tracking once ready.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Prescription Validation Needed (Clinician Query)',
                'content' => 'Hi {pharmacist_name}, the prescription for {patient_name} requires validation (missing dose/frequency). Please review and advise whether to contact prescriber.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Customer-Facing Substitute Offer',
                'content' => 'Hello {patient_name}, {product_name} is temporarily unavailable. We can offer {alternative_name} which is clinically equivalent. Reply OK to accept or ASK to speak with a pharmacist.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Delivery Attempt Failed — Action Required',
                'content' => 'Hi {pharmacist_name}, delivery attempt for order #{order_number} failed. Please advise next steps: retry, hold for pickup, or refund. Customer contact: {contact_number}.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Cold-Chain / Temperature-Sensitive Notice',
                'content' => 'Hi team, item {product_name} requires cold-chain handling. Ensure refrigerated transport and note delivery instructions in the order. Confirm pickup with the cold-chain carrier.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Return / Refund Procedure (Customer Message)',
                'content' => 'Hello {patient_name}, we’re sorry you need a return. Please keep the product sealed and reply RETURN; we’ll arrange collection and explain refund steps.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Controlled Substance Pickup Verification',
                'content' => 'Hi {pharmacist_name}, pickup for a controlled medication (order #{order_number}) scheduled. Verify patient ID at collection and record proof per regulations.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Low Stock — Restock Alert to Purchasing',
                'content' => 'Hi Purchasing, stock for {product_name} has fallen below threshold. Please reorder {qty} or advise an alternative supplier. Current last order: {last_order_date}.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Product Recall — Customer Outreach Template',
                'content' => 'Hi {pharmacist_name}, batch {batch_no} of {product_name} is recalled. Please contact affected customers with return instructions, offer replacements, and log responses. Use RECALL-CUST script for calls/messages.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Expired Stock — Segregation & Log',
                'content' => 'Team: remove expired items from the sales area, place in the EXPIRED bin, log SKU and expiry date in inventory, and notify Purchasing for disposal/replacement.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Temperature Excursion Incident Report',
                'content' => 'Attention: fridge {unit_id} recorded temp excursion at {date_time}. Secure affected products, quarantine them, photograph the unit display and notify Quality and the pharmacist immediately.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Supplier Backorder — Escalation Request',
                'content' => 'Hi Purchasing, vendor {vendor_name} reports a backorder on {product_name}. Please escalate to alternative suppliers and update operations with ETA and available substitutes.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Prescription Scan Quality Control',
                'content' => 'Reminder: scanned prescriptions must include the prescriber signature and all corners. Reject and re-scan any images that are blurred or clipped to prevent processing errors.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Fraud / Suspicious Prescription Alert',
                'content' => 'Hi {pharmacist_name}, this prescription appears suspicious (inconsistent prescriber details). Hold fulfillment, mark as PENDING_VERIFICATION and contact the prescriber for confirmation.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Daily Pack & Dispatch Quality Checklist',
                'content' => 'Team: before dispatch tick off: correct med, patient name match, expiry check, correct labelling, packaging sealed, cold-chain (if required) and record tracking number.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Controlled Drugs — Weekly Audit Reminder',
                'content' => 'Reminder: perform controlled-drug register reconciliation this week. Verify quantities, sign the audit log and report any discrepancy to the pharmacist-in-charge immediately.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'Pickup ID Verification Script (Customer Message)',
                'content' => 'Hello {patient_name}, for pickup of {medication_name} please bring valid photo ID that matches the order name. If someone else will pick up, reply AUTHORIZE with their name and ID type.',
                'target_audience' => 'technician',
            ],
            [
                'title' => 'System Downtime — Customer Advisory',
                'content' => 'Dear {patient_name}, our online ordering system is temporarily offline. We can still take orders by phone/SMS. Reply ASSIST and we’ll help place your order manually or call {phone_number}.',
                'target_audience' => 'technician',
            ],
        ]);
    }
}
