<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\Role;
use Modules\Products\Models\Category;
use Modules\Products\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles
        $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
        $userRole = Role::create(['name' => 'User', 'slug' => 'user']);
        // Seed admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
        $admin->roles()->attach($adminRole);
        // Seed categories
        $categories = Category::factory()->count(10)->create();
        // Seed products
        $products = Product::factory()->count(50)->create();
        // Attach categories to products
        $products->each(function ($product) use ($categories) {
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray(),
                ['added_at' => now()]
            );
        });

        // Sistem perpustakaan (tugas pertemuan 6)
        $this->call(LibrarySeeder::class);
    }
}
