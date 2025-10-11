<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@meiwei.com'],
            array_merge(
                User::factory()->admin()->make([
                    'name' => 'Admin User',
                    'login_name' => 'admin',
                ])->toArray(),
                ['email' => 'admin@meiwei.com']
            )
        );

        // Create some top creators (verified, high engagement)
        $topCreators = User::factory()
            ->count(5)
            ->topCreator()
            ->create();

        // Create regular verified creators
        $verifiedCreators = User::factory()
            ->count(15)
            ->verifiedCreator()
            ->create();

        // Create some premium users
        User::factory()
            ->count(20)
            ->premium()
            ->create();

        // Create regular users with varied engagement levels
        User::factory()
            ->count(30)
            ->highEngagement()
            ->create();

        // Create new users (recently joined)
        User::factory()
            ->count(20)
            ->newUser()
            ->create();

        // Create regular users
        User::factory()
            ->count(100)
            ->create();

        // Create some users with referral relationships
        $referrers = User::factory()->count(10)->create();
        foreach ($referrers as $referrer) {
            User::factory()
                ->count(rand(1, 5))
                ->withParent($referrer)
                ->create();
        }

        // Create a few suspended/banned users
        User::factory()
            ->count(3)
            ->suspended()
            ->create();

        User::factory()
            ->count(2)
            ->banned()
            ->create();

        // Create or update test user for development
        User::updateOrCreate(
            ['email' => 'test@meiwei.com'],
            array_merge(
                User::factory()->make([
                    'name' => 'Test User',
                    'login_name' => 'testuser',
                ])->toArray(),
                ['email' => 'test@meiwei.com']
            )
        );
    }
}
