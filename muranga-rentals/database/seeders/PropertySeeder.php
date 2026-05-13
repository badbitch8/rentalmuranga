<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $landlords = User::where('role', 'landlord')->get();

        if ($landlords->isEmpty()) {
            $this->command->error('No landlords found. Please run UserSeeder first.');
            return;
        }

        $properties = [
            // John Kamau's Properties
            [
                'landlord_id' => $landlords[0]->id,
                'name' => 'MUT View Apartments',
                'type' => '1_bedroom',
                'description' => 'Modern 1-bedroom apartment with stunning views of Murang\'a University. Features include spacious living area, modern kitchen, and reliable water supply. Perfect for students and young professionals.',
                'location' => 'Kiharu, Murang\'a',
                'address' => 'Off Murang\'a-Sagana Road, 500m from MUT Main Gate',
                'latitude' => -0.7167,
                'longitude' => 37.1500,
                'distance_to_mut' => 0.5,
                'price' => 8000,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 450,
                'amenities' => json_encode(['wifi', 'water', 'parking', 'security', 'balcony']),
                'utilities_included' => json_encode(['water', 'garbage_collection']),
                'rules' => 'No smoking, No pets, Quiet hours 10PM-6AM',
                'available_from' => now(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
            [
                'landlord_id' => $landlords[0]->id,
                'name' => 'Student Haven Bedsitters',
                'type' => 'bedsitter',
                'description' => 'Affordable bedsitters designed specifically for MUT students. Each unit has its own bathroom and kitchenette. Located in a secure compound with 24/7 security.',
                'location' => 'Kiharu, Murang\'a',
                'address' => 'Kenyatta Road, 300m from MUT',
                'latitude' => -0.7180,
                'longitude' => 37.1520,
                'distance_to_mut' => 0.3,
                'price' => 5000,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 250,
                'amenities' => json_encode(['wifi', 'water', 'security', 'laundry']),
                'utilities_included' => json_encode(['water']),
                'rules' => 'No loud music, Visitors allowed until 9PM',
                'available_from' => now(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
            [
                'landlord_id' => $landlords[0]->id,
                'name' => 'Campus Edge Studios',
                'type' => 'studio',
                'description' => 'Stylish studio apartments perfect for students who value privacy and comfort. Open-plan living with modern fixtures and fittings.',
                'location' => 'Kiharu, Murang\'a',
                'address' => 'University Road, Adjacent to MUT',
                'latitude' => -0.7150,
                'longitude' => 37.1510,
                'distance_to_mut' => 0.2,
                'price' => 7000,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 350,
                'amenities' => json_encode(['wifi', 'water', 'electricity_backup', 'security', 'gym']),
                'utilities_included' => json_encode(['water', 'wifi']),
                'rules' => 'No smoking indoors, Maximum 2 occupants',
                'available_from' => now()->addDays(7),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],

            // Mary Wanjiku's Properties
            [
                'landlord_id' => $landlords[1]->id,
                'name' => 'Wanjiku Residences',
                'type' => '2_bedroom',
                'description' => 'Spacious 2-bedroom apartments ideal for students who want to share costs. Each bedroom is self-contained with modern amenities throughout.',
                'location' => 'Murang\'a Town',
                'address' => 'General Kago Road, Murang\'a Town',
                'latitude' => -0.7200,
                'longitude' => 37.1480,
                'distance_to_mut' => 1.2,
                'price' => 12000,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 650,
                'amenities' => json_encode(['wifi', 'water', 'parking', 'security', 'balcony', 'kitchen_appliances']),
                'utilities_included' => json_encode(['water', 'garbage_collection', 'security']),
                'rules' => 'No parties, Visitors register at gate',
                'available_from' => now(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
            [
                'landlord_id' => $landlords[1]->id,
                'name' => 'Budget Friendly Singles',
                'type' => 'single_room',
                'description' => 'Clean and affordable single rooms with shared bathroom facilities. Perfect for budget-conscious students. Includes study desk and wardrobe.',
                'location' => 'Kiharu, Murang\'a',
                'address' => 'Ihura Road, Near MUT',
                'latitude' => -0.7190,
                'longitude' => 37.1530,
                'distance_to_mut' => 0.4,
                'price' => 3500,
                'bedrooms' => 1,
                'bathrooms' => 0,
                'square_feet' => 150,
                'amenities' => json_encode(['water', 'security', 'shared_kitchen']),
                'utilities_included' => json_encode(['water']),
                'rules' => 'Shared facilities, Keep common areas clean',
                'available_from' => now(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],

            // Peter Mwangi's Properties
            [
                'landlord_id' => $landlords[2]->id,
                'name' => 'Mwangi Apartments',
                'type' => 'apartment',
                'description' => 'Well-maintained apartments in a quiet neighborhood. Great for serious students and young professionals. Ample parking and green spaces.',
                'location' => 'Murang\'a Town',
                'address' => 'Makutano Junction, Murang\'a',
                'latitude' => -0.7220,
                'longitude' => 37.1460,
                'distance_to_mut' => 1.5,
                'price' => 10000,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'square_feet' => 550,
                'amenities' => json_encode(['wifi', 'water', 'parking', 'security', 'garden']),
                'utilities_included' => json_encode(['water', 'garbage_collection']),
                'rules' => 'No loud noise after 10PM, Pets allowed with deposit',
                'available_from' => now()->addDays(14),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
            [
                'landlord_id' => $landlords[2]->id,
                'name' => 'Shared Student Rooms',
                'type' => 'shared_room',
                'description' => 'Affordable shared accommodation for students. Each room accommodates 2 students with separate beds and storage. Shared bathroom and kitchen facilities.',
                'location' => 'Kiharu, Murang\'a',
                'address' => 'Kagio Road, 600m from MUT',
                'latitude' => -0.7170,
                'longitude' => 37.1540,
                'distance_to_mut' => 0.6,
                'price' => 2500,
                'bedrooms' => 1,
                'bathrooms' => 0,
                'square_feet' => 200,
                'amenities' => json_encode(['water', 'security', 'shared_kitchen', 'study_area']),
                'utilities_included' => json_encode(['water']),
                'rules' => 'Maximum 2 per room, Respect roommate privacy',
                'available_from' => now(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
            [
                'landlord_id' => $landlords[2]->id,
                'name' => 'Executive 3-Bedroom',
                'type' => '3_bedroom',
                'description' => 'Luxurious 3-bedroom apartment perfect for families or professionals. Features include master ensuite, modern kitchen, spacious living room, and balcony.',
                'location' => 'Murang\'a Town',
                'address' => 'Kenol-Murang\'a Road',
                'latitude' => -0.7240,
                'longitude' => 37.1440,
                'distance_to_mut' => 2.0,
                'price' => 18000,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 900,
                'amenities' => json_encode(['wifi', 'water', 'parking', 'security', 'balcony', 'kitchen_appliances', 'backup_generator']),
                'utilities_included' => json_encode(['water', 'garbage_collection', 'security', 'gardening']),
                'rules' => 'No subletting, Maintain cleanliness',
                'available_from' => now()->addMonth(),
                'is_available' => true,
                'is_verified' => true,
                'status' => 'approved',
            ],
        ];

        foreach ($properties as $propertyData) {
            $property = Property::create($propertyData);

            // Add sample images (placeholder URLs)
            $imageCount = rand(3, 5);
            for ($i = 1; $i <= $imageCount; $i++) {
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_path' => "properties/sample-{$i}.jpg",
                    'is_primary' => $i === 1,
                    'order' => $i,
                ]);
            }
        }

        $this->command->info('Properties seeded successfully!');
        $this->command->info('Created ' . count($properties) . ' properties with images.');
    }
}

// Made with Bob
