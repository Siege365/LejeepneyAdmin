<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Landmark;

class LandmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $landmarks = [
            // 🏙 Downtown / City Center
            [
                'name' => 'People\'s Park',
                'latitude' => 7.0736,
                'longitude' => 125.6110,
                'description' => 'A 4-hectare urban park in the heart of Davao City featuring sculptures, fountains, and green spaces.',
                'category' => 'city_center',
                'is_featured' => true,
            ],
            [
                'name' => 'San Pedro Cathedral',
                'latitude' => 7.0644,
                'longitude' => 125.6089,
                'description' => 'The oldest church in Mindanao, built in 1847. A historical and religious landmark.',
                'category' => 'city_center',
                'is_featured' => true,
            ],
            [
                'name' => 'Magsaysay Park',
                'latitude' => 7.0772,
                'longitude' => 125.6156,
                'description' => 'A waterfront park along Davao Gulf, popular for evening strolls and events.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Roxas Night Market',
                'latitude' => 7.0739,
                'longitude' => 125.6142,
                'description' => 'Famous night market along Roxas Avenue offering street food, shopping, and entertainment.',
                'category' => 'city_center',
                'is_featured' => true,
            ],
            [
                'name' => 'Rizal Park',
                'latitude' => 7.0707,
                'longitude' => 125.6084,
                'description' => 'Historic park featuring the Jose Rizal monument and open spaces for recreation.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Davao City Hall',
                'latitude' => 7.0747,
                'longitude' => 125.6115,
                'description' => 'The seat of the city government, located near People\'s Park.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Davao Museum',
                'latitude' => 7.0756,
                'longitude' => 125.6121,
                'description' => 'Museum showcasing Davao\'s cultural heritage and history.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Bankerohan Public Market',
                'latitude' => 7.0681,
                'longitude' => 125.6078,
                'description' => 'The largest wet market in Davao City, open 24/7 selling fresh produce and goods.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Aldevinco Shopping Center',
                'latitude' => 7.0728,
                'longitude' => 125.6132,
                'description' => 'Popular shopping destination for souvenirs, handicrafts, and local products.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Crocodile Park',
                'latitude' => 7.1067,
                'longitude' => 125.6456,
                'description' => 'Wildlife park featuring crocodiles and other animals, educational tours available.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'D\' Bone Collector Museum',
                'latitude' => 7.1075,
                'longitude' => 125.6289,
                'description' => 'Natural history museum featuring the largest collection of bones and skeletons in the Philippines.',
                'category' => 'city_center',
                'is_featured' => false,
            ],
            [
                'name' => 'Jack\'s Ridge',
                'latitude' => 7.0936,
                'longitude' => 125.6342,
                'description' => 'Hilltop resort and restaurant offering panoramic views of Davao City and Gulf.',
                'category' => 'city_center',
                'is_featured' => true,
            ],

            // 🛍 Malls & Commercial
            [
                'name' => 'SM Lanang Premier',
                'latitude' => 7.1006,
                'longitude' => 125.6447,
                'description' => 'Largest shopping mall in Mindanao with entertainment, dining, and retail options.',
                'category' => 'mall',
                'is_featured' => true,
            ],
            [
                'name' => 'Abreeza Mall',
                'latitude' => 7.0856,
                'longitude' => 125.6189,
                'description' => 'Premium shopping mall in J.P. Laurel Avenue, known for its modern architecture.',
                'category' => 'mall',
                'is_featured' => true,
            ],
            [
                'name' => 'Gaisano Mall Davao',
                'latitude' => 7.0742,
                'longitude' => 125.6127,
                'description' => 'One of the oldest and most popular malls in downtown Davao.',
                'category' => 'mall',
                'is_featured' => false,
            ],
            [
                'name' => 'Victoria Plaza',
                'latitude' => 7.0751,
                'longitude' => 125.6129,
                'description' => 'Shopping mall in the heart of the city offering various retail stores.',
                'category' => 'mall',
                'is_featured' => false,
            ],
            [
                'name' => 'NCCC Mall Buhangin',
                'latitude' => 7.1175,
                'longitude' => 125.6453,
                'description' => 'Major shopping destination in northern Davao with supermarket and retail stores.',
                'category' => 'mall',
                'is_featured' => false,
            ],
            [
                'name' => 'Robinsons Cybergate',
                'latitude' => 7.0650,
                'longitude' => 125.6073,
                'description' => 'Shopping mall featuring tech stores, entertainment, and dining options.',
                'category' => 'mall',
                'is_featured' => false,
            ],
            [
                'name' => 'Felcris Centrale',
                'latitude' => 7.0729,
                'longitude' => 125.6096,
                'description' => 'Shopping center known for affordable goods and local products.',
                'category' => 'mall',
                'is_featured' => false,
            ],

            // 🏫 Schools & Institutions
            [
                'name' => 'University of the Philippines Mindanao',
                'latitude' => 7.0667,
                'longitude' => 125.5950,
                'description' => 'Premier state university in Mindanao offering undergraduate and graduate programs.',
                'category' => 'school',
                'is_featured' => true,
            ],
            [
                'name' => 'Ateneo de Davao University',
                'latitude' => 7.0692,
                'longitude' => 125.6089,
                'description' => 'Leading Jesuit university in Mindanao known for academic excellence.',
                'category' => 'school',
                'is_featured' => true,
            ],
            [
                'name' => 'University of Mindanao',
                'latitude' => 7.0633,
                'longitude' => 125.6117,
                'description' => 'Large private university with multiple campuses across Davao City.',
                'category' => 'school',
                'is_featured' => false,
            ],
            [
                'name' => 'University of Southeastern Philippines',
                'latitude' => 7.0728,
                'longitude' => 125.6075,
                'description' => 'State university offering various programs in education, engineering, and more.',
                'category' => 'school',
                'is_featured' => false,
            ],
            [
                'name' => 'Philippine Women\'s College',
                'latitude' => 7.0764,
                'longitude' => 125.6128,
                'description' => 'Private educational institution focusing on quality education.',
                'category' => 'school',
                'is_featured' => false,
            ],
            [
                'name' => 'Holy Cross of Davao College',
                'latitude' => 7.1161,
                'longitude' => 125.6403,
                'description' => 'Catholic educational institution offering primary to tertiary education.',
                'category' => 'school',
                'is_featured' => false,
            ],
            [
                'name' => 'Davao Doctors College',
                'latitude' => 7.0750,
                'longitude' => 125.6139,
                'description' => 'Medical and health sciences college in downtown Davao.',
                'category' => 'school',
                'is_featured' => false,
            ],

            // 🏥 Hospitals
            [
                'name' => 'Southern Philippines Medical Center',
                'latitude' => 7.0764,
                'longitude' => 125.6139,
                'description' => 'The largest tertiary government hospital in Mindanao.',
                'category' => 'hospital',
                'is_featured' => true,
            ],
            [
                'name' => 'Davao Doctors Hospital',
                'latitude' => 7.0753,
                'longitude' => 125.6142,
                'description' => 'Leading private hospital providing comprehensive medical services.',
                'category' => 'hospital',
                'is_featured' => true,
            ],
            [
                'name' => 'Davao Medical School Foundation Hospital',
                'latitude' => 7.1050,
                'longitude' => 125.6225,
                'description' => 'Teaching hospital affiliated with DMSF College of Medicine.',
                'category' => 'hospital',
                'is_featured' => false,
            ],
            [
                'name' => 'Brokenshire Hospital',
                'latitude' => 7.0742,
                'longitude' => 125.6136,
                'description' => 'Mission hospital providing quality healthcare services since 1924.',
                'category' => 'hospital',
                'is_featured' => false,
            ],
            [
                'name' => 'San Pedro Hospital',
                'latitude' => 7.0719,
                'longitude' => 125.6128,
                'description' => 'Community hospital offering various medical specialties.',
                'category' => 'hospital',
                'is_featured' => false,
            ],
            [
                'name' => 'Mindanao Orthopedic Sports and Rehabilitation Center',
                'latitude' => 7.0861,
                'longitude' => 125.6178,
                'description' => 'Specialized hospital for orthopedic care and sports medicine.',
                'category' => 'hospital',
                'is_featured' => false,
            ],

            // 🚌 Transport & Terminals
            [
                'name' => 'Ecoland Terminal',
                'latitude' => 7.0764,
                'longitude' => 125.6100,
                'description' => 'Major jeepney and bus terminal serving routes to northern Davao and provinces.',
                'category' => 'transport',
                'is_featured' => true,
            ],
            [
                'name' => 'Overland Terminal',
                'latitude' => 7.0628,
                'longitude' => 125.6083,
                'description' => 'Main terminal for buses going to other cities in Mindanao.',
                'category' => 'transport',
                'is_featured' => true,
            ],
        ];

        foreach ($landmarks as $landmark) {
            Landmark::create($landmark);
        }
    }
}
