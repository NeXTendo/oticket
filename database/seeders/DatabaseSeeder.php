<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Organizer;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users
        $customer = User::firstOrCreate(
            ['email' => 'customer@oticket.com'],
            [
                'name' => 'Mwila Mwansa',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $organizerUser = User::firstOrCreate(
            ['email' => 'organizer@oticket.com'],
            [
                'name' => 'Chileshe Banda',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Organizers
        $afroBeatsOrg = Organizer::firstOrCreate(
            ['slug' => 'afrobeats-live-zambia'],
            [
                'user_id' => $organizerUser->id,
                'name' => 'AfroBeats Live Zambia',
                'contact_email' => 'events@afrobeats.co.zm',
                'contact_phone' => '+260977112233',
                'status' => 'active',
                'commission_rate' => 5.00,
                'tier' => 'premium',
            ]
        );

        $techOrg = Organizer::firstOrCreate(
            ['slug' => 'innovate-zambia'],
            [
                'user_id' => $organizerUser->id,
                'name' => 'Innovate Africa Hub',
                'contact_email' => 'hello@innovate.org.zm',
                'contact_phone' => '+260966554433',
                'status' => 'active',
                'commission_rate' => 4.50,
                'tier' => 'partner',
            ]
        );

        $lifestyleOrg = Organizer::firstOrCreate(
            ['slug' => 'livingstone-festivals'],
            [
                'user_id' => $organizerUser->id,
                'name' => 'Vic Falls Collective',
                'contact_email' => 'info@vicfallsevents.com',
                'contact_phone' => '+260955998877',
                'status' => 'active',
                'commission_rate' => 5.00,
                'tier' => 'standard',
            ]
        );

        // 3. Create Events & Ticket Types
        $eventsData = [
            [
                'organizer_id' => $afroBeatsOrg->id,
                'name' => 'Lusaka Music Carnival 2026',
                'slug' => 'lusaka-music-carnival-2026',
                'category' => 'music',
                'description' => 'The biggest live outdoor music experience in Zambia featuring top African headliners, electric DJ sets, vibrant cultural fashion, food stalls, and an unforgettable festival atmosphere.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'National Heroes Stadium Grounds',
                'venue_address' => 'Great North Road, Lusaka',
                'starts_at' => now()->addDays(5)->setHour(16)->setMinute(0),
                'ends_at' => now()->addDays(6)->setHour(3)->setMinute(0),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'Early Bird (Limited)', 'description' => 'Discounted general access pass.', 'price' => 150.00, 'total' => 200, 'max' => 6],
                    ['name' => 'General Admission', 'description' => 'Full festival grounds access & concert stage area.', 'price' => 250.00, 'total' => 1500, 'max' => 10],
                    ['name' => 'VIP Golden Circle', 'description' => 'Front stage viewing, private bar, express entry & lounge access.', 'price' => 650.00, 'total' => 250, 'max' => 4],
                    ['name' => 'VVIP Table of 4', 'description' => 'Reserved booth table, bottle service & dedicated waiter.', 'price' => 3200.00, 'total' => 20, 'max' => 2],
                ],
            ],
            [
                'organizer_id' => $techOrg->id,
                'name' => 'Zambia Tech & AI Summit 2026',
                'slug' => 'zambia-tech-ai-summit-2026',
                'category' => 'tech',
                'description' => 'Connecting African founders, software engineers, fintech disruptors, and global investors. Keynotes on AI in emerging markets, fintech rails, and cross-border innovation.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'Mulungushi International Conference Centre',
                'venue_address' => 'Great East Road, Lusaka',
                'starts_at' => now()->addDays(12)->setHour(9)->setMinute(0),
                'ends_at' => now()->addDays(13)->setHour(17)->setMinute(30),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'Student Pass', 'description' => 'Valid student ID required at gate.', 'price' => 80.00, 'total' => 150, 'max' => 2],
                    ['name' => 'Delegate Pass', 'description' => 'All sessions, workshops, networking lunch & expo floor.', 'price' => 450.00, 'total' => 600, 'max' => 8],
                    ['name' => 'Executive & Investor Pass', 'description' => 'VIP networking dinner, private investor lounge & direct founder intros.', 'price' => 1200.00, 'total' => 100, 'max' => 4],
                ],
            ],
            [
                'organizer_id' => $lifestyleOrg->id,
                'name' => 'Victoria Falls Sunset & Sound Festival',
                'slug' => 'vic-falls-sunset-sound-festival',
                'category' => 'festival',
                'description' => 'Experience sunset over the Batoka Gorge with acoustic sets, deep house grooves, and craft cocktail bars against the backdrop of the mighty Smoke that Thunders.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'The Lookout Deck & Gorge Viewpoint',
                'venue_address' => 'Livingstone, Zambia',
                'starts_at' => now()->addDays(20)->setHour(15)->setMinute(30),
                'ends_at' => now()->addDays(20)->setHour(23)->setMinute(0),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'Sunset Pass', 'description' => 'General lawn admission with welcome beverage.', 'price' => 300.00, 'total' => 300, 'max' => 6],
                    ['name' => 'Gorge VIP Deck', 'description' => 'Canapes, open cocktail hour and elevated sunset view.', 'price' => 850.00, 'total' => 80, 'max' => 4],
                ],
            ],
            [
                'organizer_id' => $afroBeatsOrg->id,
                'name' => 'Taste of Lusaka: Food & Wine Expo',
                'slug' => 'taste-of-lusaka-food-wine-expo',
                'category' => 'arts',
                'description' => 'Savor exquisite dishes from top Zambian chefs, artisanal wine tastings, live acoustic jazz, and craft brew masterclasses.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'EastPark Mall Piazza',
                'venue_address' => 'Thabo Mbeki Road, Lusaka',
                'starts_at' => now()->addDays(8)->setHour(11)->setMinute(0),
                'ends_at' => now()->addDays(8)->setHour(21)->setMinute(0),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'Entry + 5 Tasting Tokens', 'description' => 'Includes souvenir glass and tasting tokens.', 'price' => 180.00, 'total' => 400, 'max' => 6],
                    ['name' => 'Connoisseur VIP', 'description' => 'Unlimited tastings, private cellar masterclass & lounge.', 'price' => 500.00, 'total' => 100, 'max' => 4],
                ],
            ],
            [
                'organizer_id' => $lifestyleOrg->id,
                'name' => 'Copperbelt Football Classic',
                'slug' => 'copperbelt-football-classic',
                'category' => 'sports',
                'description' => 'The ultimate rivalry match live under the floodlights. Thrilling football action with pre-match fan festival and entertainment.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'Levy Mwanawasa Stadium',
                'venue_address' => 'Ndola, Copperbelt',
                'starts_at' => now()->addDays(15)->setHour(14)->setMinute(30),
                'ends_at' => now()->addDays(15)->setHour(18)->setMinute(0),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'Open Wings Stand', 'description' => 'Open seating bleachers.', 'price' => 50.00, 'total' => 5000, 'max' => 10],
                    ['name' => 'Grandstand Covered', 'description' => 'Covered central stadium seats.', 'price' => 120.00, 'total' => 1200, 'max' => 6],
                    ['name' => 'VIP Presidential Suite', 'description' => 'Catering, luxury lounge and executive stadium view.', 'price' => 600.00, 'total' => 80, 'max' => 4],
                ],
            ],
            [
                'organizer_id' => $afroBeatsOrg->id,
                'name' => 'Late Night Groove: Amapiano & Afro-Tech',
                'slug' => 'late-night-groove-amapiano-afrotech',
                'category' => 'nightlife',
                'description' => 'Lusaka nightlife unleashed. High-energy basslines, top resident DJs, dynamic light shows, and premium VIP booths.',
                'cover_image_path' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=1200&q=80',
                'venue_name' => 'The Sky Bar Lusaka',
                'venue_address' => 'Kafue Road, Lusaka',
                'starts_at' => now()->addDays(3)->setHour(21)->setMinute(0),
                'ends_at' => now()->addDays(4)->setHour(5)->setMinute(0),
                'status' => 'published',
                'tickets' => [
                    ['name' => 'General Ticket', 'description' => 'Standard club entrance.', 'price' => 100.00, 'total' => 400, 'max' => 6],
                    ['name' => 'VIP Skip-the-Line', 'description' => 'Fast-track entry & mezzanine access.', 'price' => 250.00, 'total' => 150, 'max' => 4],
                ],
            ],
        ];

        foreach ($eventsData as $eventData) {
            $tickets = $eventData['tickets'];
            unset($eventData['tickets']);

            $event = Event::updateOrCreate(
                ['slug' => $eventData['slug']],
                $eventData
            );

            foreach ($tickets as $t) {
                TicketType::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'name' => $t['name'],
                    ],
                    [
                        'description' => $t['description'],
                        'price' => $t['price'],
                        'quantity_total' => $t['total'],
                        'quantity_sold' => 0,
                        'quantity_reserved' => 0,
                        'max_per_order' => $t['max'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
