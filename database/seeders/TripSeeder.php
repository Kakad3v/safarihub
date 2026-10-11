<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\User;
use App\Support\PlaceholderArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $operators = collect([
            'coastline' => ['Coastline Safaris', 'coastline@example.com', 'Mombasa', ['Safaris', 'Beach stays']],
            'pwani' => ['Pwani Dhow Tours', 'pwani@example.com', 'Kwale', ['Boat trips', 'Day trips']],
            'taita' => ['Taita Trails', 'taita@example.com', 'Taita Taveta', ['Hikes']],
        ])->map(function (array $o) {
            [$name, $email, $county, $offerings] = $o;

            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'role' => 'operator']);
            $user->operatorProfile()->updateOrCreate([], [
                'business_name' => $name,
                'county' => $county,
                'offerings' => $offerings,
            ]);

            return $user;
        });

        $travelers = collect([
            'wanjiru' => ['Wanjiru', 'wanjiru@example.com'],
            'brian' => ['Brian', 'brian@example.com'],
            'amina' => ['Amina', 'amina@example.com'],
            'otieno' => ['Otieno', 'otieno@example.com'],
            'zawadi' => ['Zawadi', 'zawadi@example.com'],
        ])->map(fn(array $t) => User::firstOrCreate(['email' => $t[1]], ['name' => $t[0], 'role' => 'traveler']));

        foreach ($this->packages() as $data) {
            $package = Package::updateOrCreate(
                ['slug' => $data['slug']],
                Arr::except($data, ['slug', 'operator', 'art', 'dates', 'reviews']) + [
                    'user_id' => $operators[$data['operator']]->id,
                    'languages' => ['English', 'Swahili'],
                    'status' => 'published',
                ]
            );

            $package->photos()->delete();
            foreach ($data['art'] as $position => [$scene, $variant]) {
                $package->photos()->create([
                    'path' => PlaceholderArt::path($scene, $variant),
                    'alt' => PlaceholderArt::alt($scene),
                    'position' => $position,
                ]);
            }

            if ($package->departures()->doesntExist()) {
                foreach ($data['dates'] as [$days, $seats]) {
                    $start = now()->addDays($days)->startOfDay();
                    $package->departures()->create([
                        'starts_on' => $start,
                        'ends_on' => $start->copy()->addDays($data['duration_days'] - 1),
                        'seats' => $seats,
                    ]);
                }
            }

            if ($package->reviews()->doesntExist()) {
                foreach ($data['reviews'] as [$who, $rating, $body, $monthsAgo]) {
                    $package->reviews()->create([
                        'user_id' => $travelers[$who]->id,
                        'rating' => $rating,
                        'body' => $body,
                        'travelled_on' => now()->subMonths($monthsAgo),
                    ]);
                }
            }
        }
    }

    private function packages(): array
    {
        return [
            [
                'slug' => 'masai-mara-3-day-safari',
                'operator' => 'coastline',
                'type' => 'trip',
                'title' => '3-day safari in the Masai Mara',
                'category' => 'Safaris',
                'place' => 'Masai Mara',
                'county' => 'Narok County',
                'description' => "Three days of game drives in the Masai Mara with a local guide, sleeping in a tented camp on the edge of the reserve. You travel in a small group in a 4x4 with a pop-up roof, so everyone gets a window seat.\n\nCheetah, giraffe, elephant and lion are regular sightings. Wildebeest herds cross the Mara River from July to October.",
                'duration_days' => 3,
                'nights' => 2,
                'max_group' => 6,
                'price' => 28500,
                'pickup_location' => 'Westlands, Nairobi',
                'pickup_time' => '6:00 am',
                'includes' => ['Park fees', 'Game drives in a 4x4', 'Tented camp, 2 nights', 'All meals and drinking water', 'Pickup and drop-off in Nairobi'],
                'excludes' => ['Drinks at camp', 'Tips for guide and staff', 'Maasai village visit fee', 'Travel insurance'],
                'itinerary' => [
                    ['title' => 'Day 1: Nairobi to the Mara', 'body' => 'Leave Nairobi at 6:00 am, stop for breakfast at the escarpment, and arrive at camp for lunch. Afternoon game drive until sunset.'],
                    ['title' => 'Day 2: A full day on the plains', 'body' => 'Sunrise game drive, a picnic lunch in the bush, and an optional visit to a Maasai village in the afternoon.'],
                    ['title' => 'Day 3: Sunrise drive and return', 'body' => 'A last early game drive, breakfast at camp, then the drive back. You reach Nairobi around 6:00 pm.'],
                ],
                'featured' => true,
                'featured_position' => 1,
                'art' => [['savanna', 'day'], ['savanna', 'golden'], ['camp', 'dusk']],
                'dates' => [[33, 6], [56, 2], [70, 6]],
                'reviews' => [
                    ['wanjiru', 5, 'The guide knew every animal by name and the price was exactly what we paid. No surprises.', 1],
                    ['brian', 5, 'Booked on my phone in five minutes and chatted with the operator before paying. Camp was lovely.', 2],
                    ['amina', 4, 'Great drives. The road from Nairobi was long, so bring snacks and a neck pillow.', 3],
                ],
            ],
            [
                'slug' => 'kisite-mpunguti-dolphins-and-reef-diving',
                'operator' => 'pwani',
                'type' => 'experience',
                'title' => 'Dolphins and reef diving day trip',
                'category' => 'Boat trips',
                'place' => 'Kisite Mpunguti',
                'county' => 'Kwale County',
                'description' => "Leave Shimoni at dawn on a traditional dhow to look for dolphins in the Kisite Mpunguti Marine Park. Then snorkel or dive over the coral gardens with a guide.\n\nLunch of grilled fish is served on Wasini Island before the return to the jetty.",
                'duration_days' => 1,
                'nights' => 0,
                'max_group' => 12,
                'price' => 6500,
                'pickup_location' => 'Shimoni jetty',
                'pickup_time' => '6:30 am',
                'includes' => ['Marine park fees', 'Dhow and crew', 'Snorkel mask and fins', 'Grilled fish lunch'],
                'excludes' => ['Scuba gear hire', 'Drinks', 'Tips for the crew'],
                'itinerary' => [
                    ['title' => 'Dawn: dolphin watching', 'body' => 'The dhow heads out early, when the dolphins are most active.'],
                    ['title' => 'Mid morning: snorkelling on the reef', 'body' => 'A guide stays in the water with beginners and points out the fish.'],
                    ['title' => 'Midday: lunch on Wasini Island', 'body' => 'Grilled fish and rice, then a short walk before heading back.'],
                ],
                'featured' => true,
                'featured_position' => 3,
                'art' => [['sea', 'day'], ['sea', 'golden'], ['beach', 'day']],
                'dates' => [[10, 12], [17, 12], [24, 4], [31, 12]],
                'reviews' => [
                    ['brian', 5, 'We swam with dolphins at sunrise, then snorkelled over the reef. The crew looked after everyone.', 1],
                    ['zawadi', 5, 'Calm sea, clear water and a great lunch. The kids loved the dolphins.', 2],
                ],
            ],
            [
                'slug' => 'diani-beach-escape-2-nights',
                'operator' => 'coastline',
                'type' => 'trip',
                'title' => 'Beach escape, 2 nights',
                'category' => 'Beach stays',
                'place' => 'Diani',
                'county' => 'Kwale County',
                'description' => "Two nights by the sea on Diani Beach, with breakfast each morning and the afternoons free for swimming, kite lessons or a walk along the sand.\n\nA driver collects you from Mombasa and brings you back on the last day.",
                'duration_days' => 3,
                'nights' => 2,
                'max_group' => 6,
                'price' => 14000,
                'pickup_location' => 'Nyali, Mombasa',
                'pickup_time' => '9:00 am',
                'includes' => ['2 nights in a beach hotel', 'Breakfast daily', 'Transfers from Mombasa', 'Sunset walk with a local guide'],
                'excludes' => ['Lunch and dinner', 'Water sports', 'Tips'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrive and settle in', 'body' => 'Pickup in Mombasa, check in, and a first swim before sunset.'],
                    ['title' => 'Day 2: A free day on the beach', 'body' => 'Swim, sunbathe, or ask your guide about kite lessons and dhow rides.'],
                    ['title' => 'Day 3: Breakfast and return', 'body' => 'A relaxed breakfast, then the drive back to Mombasa.'],
                ],
                'featured' => true,
                'featured_position' => 2,
                'art' => [['beach', 'day'], ['beach', 'golden'], ['beach', 'dusk']],
                'dates' => [[12, 6], [26, 6], [40, 3]],
                'reviews' => [
                    ['amina', 5, 'A quiet stretch of white sand and an easy booking made the whole trip. Fair price and a helpful operator.', 1],
                    ['otieno', 4, 'Lovely beach and friendly staff. Lunch options nearby were pricier than expected.', 3],
                ],
            ],
            [
                'slug' => 'mpunguti-snorkelling-day-trip',
                'operator' => 'pwani',
                'type' => 'experience',
                'title' => 'Snorkelling day trip',
                'category' => 'Day trips',
                'place' => 'Kisite Mpunguti',
                'county' => 'Kwale County',
                'description' => "A relaxed half day of snorkelling over the coral gardens of the marine park. Guides help beginners with masks and fins and point out the fish.\n\nYou are back at the jetty by early afternoon.",
                'duration_days' => 1,
                'nights' => 0,
                'max_group' => 10,
                'price' => 5500,
                'pickup_location' => 'Shimoni jetty',
                'pickup_time' => '8:00 am',
                'includes' => ['Marine park fees', 'Boat and crew', 'Snorkel gear', 'Bottled water'],
                'excludes' => ['Lunch', 'Underwater camera hire', 'Tips'],
                'itinerary' => [
                    ['title' => 'Morning: boat to the reef', 'body' => 'A short ride out, with a safety briefing and help fitting your gear.'],
                    ['title' => 'Late morning: snorkelling', 'body' => 'Two sessions over the coral, with a break on the boat in between.'],
                ],
                'featured' => false,
                'art' => [['sea', 'golden'], ['sea', 'day']],
                'dates' => [[8, 10], [15, 10], [22, 2]],
                'reviews' => [
                    ['otieno', 5, 'Great for beginners. The guide stayed in the water with us the whole time.', 2],
                ],
            ],
            [
                'slug' => 'chullu-hills-sunrise-hike',
                'operator' => 'taita',
                'type' => 'experience',
                'title' => 'Sunrise hike',
                'category' => 'Hikes',
                'place' => 'Chullu hills',
                'county' => 'Taita Taveta County',
                'description' => "Climb the Chullu hills before dawn and watch the sun rise over the Tsavo plains. The route is steady and suits most fitness levels.\n\nA local guide leads the walk and explains the plants and wildlife on the way.",
                'duration_days' => 1,
                'nights' => 0,
                'max_group' => 8,
                'price' => 3500,
                'pickup_location' => 'Voi town',
                'pickup_time' => '4:30 am',
                'includes' => ['Local guide', 'Hot drinks and snacks at the top', 'Pickup from Voi', 'Park and community fees'],
                'excludes' => ['Meals', 'Hiking poles', 'Tips'],
                'itinerary' => [
                    ['title' => 'Before dawn: meet and start the climb', 'body' => 'Meet in Voi and drive to the trailhead. The climb takes about two hours.'],
                    ['title' => 'Sunrise: breakfast at the summit', 'body' => 'Hot tea and snacks while the sun comes up over the plains.'],
                    ['title' => 'Mid morning: walk back down', 'body' => 'An easier descent, with time for photos along the way.'],
                ],
                'featured' => false,
                'art' => [['hills', 'golden'], ['hills', 'day'], ['hills', 'dusk']],
                'dates' => [[9, 8], [16, 8], [23, 8], [30, 8]],
                'reviews' => [
                    ['zawadi', 5, 'Sunrise from the top was worth the early start. Our guide kept the pace easy for everyone.', 1],
                    ['brian', 4, 'Steep in places, so wear proper shoes.', 2],
                ],
            ],
            [
                'slug' => 'masai-mara-tented-camp-2-nights',
                'operator' => 'coastline',
                'type' => 'trip',
                'title' => 'Tented camp, 2 nights',
                'category' => 'Safaris',
                'place' => 'Masai Mara',
                'county' => 'Narok County',
                'description' => "A slower safari from a comfortable tented camp on a hillside above the Mara. Wake to birdsong, take two game drives a day and finish with dinner by the fire.\n\nTents have real beds, hot showers and a private deck.",
                'duration_days' => 3,
                'nights' => 2,
                'max_group' => 4,
                'price' => 38000,
                'pickup_location' => 'Westlands, Nairobi',
                'pickup_time' => '6:00 am',
                'includes' => ['Tented camp, 2 nights', 'All meals', 'Two game drives a day', 'Park fees'],
                'excludes' => ['Drinks', 'Flights', 'Tips'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrive at camp', 'body' => 'The drive from Nairobi, lunch at camp and an afternoon game drive.'],
                    ['title' => 'Day 2: Two game drives', 'body' => 'Sunrise and late afternoon drives, with a long lunch and rest at camp in between.'],
                    ['title' => 'Day 3: Breakfast and return', 'body' => 'A last morning drive, then the journey back to Nairobi.'],
                ],
                'featured' => false,
                'art' => [['camp', 'dusk'], ['savanna', 'golden'], ['savanna', 'day']],
                'dates' => [[45, 4], [75, 4]],
                'reviews' => [
                    ['wanjiru', 5, 'Waking up on our own deck with giraffes in the distance was the highlight.', 1],
                ],
            ],
            [
                'slug' => 'diani-sunset-dhow-cruise',
                'operator' => 'pwani',
                'type' => 'experience',
                'title' => 'Sunset dhow cruise',
                'category' => 'Boat trips',
                'place' => 'Diani',
                'county' => 'Kwale County',
                'description' => "Sail along the Diani coast on a traditional dhow as the sun goes down. Drinks and snacks are served on board while the crew points out the reef and the shoreline.\n\nYou are back on the beach after dark.",
                'duration_days' => 1,
                'nights' => 0,
                'max_group' => 10,
                'price' => 4000,
                'pickup_location' => 'Diani Beach Road jetty',
                'pickup_time' => '4:30 pm',
                'includes' => ['Dhow and crew', 'Soft drinks and snacks', 'Life jackets'],
                'excludes' => ['Alcohol', 'Dinner', 'Tips'],
                'itinerary' => [
                    ['title' => 'Late afternoon: board and set sail', 'body' => 'A safety briefing, then the dhow heads out along the coast.'],
                    ['title' => 'Sunset: on the water', 'body' => 'Drinks and snacks while the sun goes down. Back on the beach after dark.'],
                ],
                'featured' => false,
                'art' => [['sea', 'dusk'], ['sea', 'golden'], ['beach', 'dusk']],
                'dates' => [[6, 10], [7, 10], [13, 10], [14, 10]],
                'reviews' => [
                    ['amina', 5, 'Golden light on the water and a very relaxed crew. Perfect last evening.', 1],
                ],
            ],
        ];
    }
}
