<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@murangarentals.com',
            'phone_number' => '254712345678',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        // Create Landlords
        $landlords = [
            [
                'name' => 'John Kamau',
                'email' => 'john.kamau@gmail.com',
                'phone_number' => '254722111222',
                'password' => Hash::make('password123'),
                'role' => 'landlord',
                'email_verified_at' => now(),
                'is_verified' => true,
                'id_number' => '12345678',
                'bio' => 'Experienced landlord with multiple properties near MUT. Committed to providing quality housing for students.',
            ],
            [
                'name' => 'Mary Wanjiku',
                'email' => 'mary.wanjiku@gmail.com',
                'phone_number' => '254733222333',
                'password' => Hash::make('password123'),
                'role' => 'landlord',
                'email_verified_at' => now(),
                'is_verified' => true,
                'id_number' => '23456789',
                'bio' => 'Property owner specializing in student accommodation. All properties within walking distance to MUT.',
            ],
            [
                'name' => 'Peter Mwangi',
                'email' => 'peter.mwangi@gmail.com',
                'phone_number' => '254744333444',
                'password' => Hash::make('password123'),
                'role' => 'landlord',
                'email_verified_at' => now(),
                'is_verified' => true,
                'id_number' => '34567890',
                'bio' => 'Affordable housing provider for students and young professionals in Murang\'a.',
            ],
        ];

        foreach ($landlords as $landlord) {
            User::create($landlord);
        }

        // Create Tenants (MUT Students)
        $tenants = [
            [
                'name' => 'James Omondi',
                'email' => 'james.omondi@student.mut.ac.ke',
                'phone_number' => '254755444555',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'student_id' => 'MUT/2023/001',
                'bio' => 'Third year Computer Science student at MUT looking for affordable accommodation.',
            ],
            [
                'name' => 'Grace Akinyi',
                'email' => 'grace.akinyi@student.mut.ac.ke',
                'phone_number' => '254766555666',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'student_id' => 'MUT/2023/002',
                'bio' => 'Second year Business student seeking clean and safe accommodation near campus.',
            ],
            [
                'name' => 'David Kipchoge',
                'email' => 'david.kipchoge@student.mut.ac.ke',
                'phone_number' => '254777666777',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'student_id' => 'MUT/2023/003',
                'bio' => 'First year Engineering student looking for shared accommodation to save costs.',
            ],
            [
                'name' => 'Sarah Njeri',
                'email' => 'sarah.njeri@student.mut.ac.ke',
                'phone_number' => '254788777888',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'student_id' => 'MUT/2023/004',
                'bio' => 'Fourth year Education student preferring single rooms close to campus.',
            ],
            [
                'name' => 'Michael Otieno',
                'email' => 'michael.otieno@student.mut.ac.ke',
                'phone_number' => '254799888999',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'student_id' => 'MUT/2023/005',
                'bio' => 'Graduate student looking for quiet accommodation for research work.',
            ],
        ];

        foreach ($tenants as $tenant) {
            User::create($tenant);
        }

        // Create Local Professionals
        $professionals = [
            [
                'name' => 'Ann Wambui',
                'email' => 'ann.wambui@gmail.com',
                'phone_number' => '254700111222',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'bio' => 'Young professional working in Murang\'a town seeking modern apartment.',
            ],
            [
                'name' => 'Robert Kariuki',
                'email' => 'robert.kariuki@gmail.com',
                'phone_number' => '254711222333',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'email_verified_at' => now(),
                'bio' => 'IT professional looking for furnished apartment with good internet connectivity.',
            ],
        ];

        foreach ($professionals as $professional) {
            User::create($professional);
        }

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@murangarentals.com / password123');
        $this->command->info('Landlords: john.kamau@gmail.com, mary.wanjiku@gmail.com, peter.mwangi@gmail.com / password123');
        $this->command->info('Tenants: james.omondi@student.mut.ac.ke, grace.akinyi@student.mut.ac.ke, etc. / password123');
    }
}

// Made with Bob
