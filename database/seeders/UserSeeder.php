<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts. Every account uses the password "password".
     * Roles: admin (reviews and manages everything), writer (writes and publishes own articles), reader (comments).
     */
    public function run(): void
    {
        $users = [
            ['TechNova Admin', 'admin@technova.com', 'admin', 'Management', 'Oversees the newsroom: reviews articles and manages accounts.'],
            ['Ahmad Saleh', 'writer@technova.com', 'writer', 'Media & Content', 'Writes about the engineering team and internal tools.'],
            ['Rana Haddad', 'rana@technova.com', 'writer', 'People & Culture', 'Covers company events, hiring and team stories.'],
            ['Samer Khoury', 'reader@technova.com', 'reader', 'Engineering', 'Backend engineer on the main product team.'],
            ['Maya Nasser', 'maya@technova.com', 'reader', 'Design', 'Product designer who follows the engineering updates.'],
        ];

        foreach ($users as [$name, $email, $role, $department, $bio]) {
            [$first, $last] = explode(' ', $name, 2);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $user->profile()->create([
                'first_name' => $first,
                'last_name' => $last,
                'bio' => $bio,
                'department' => $department,
            ]);
        }
    }
}
