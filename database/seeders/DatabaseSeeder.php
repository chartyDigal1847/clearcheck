<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Temporary user for testing - will be replaced by SSO
        // This creates a basic user structure that SSO can populate later
        
        // You can uncomment these lines if you need test data during development:
        $this->call([
      UserSeeder::class,
     StudentSeeder::class,
           CheckerSeeder::class,
    
      ]);
    }
}
