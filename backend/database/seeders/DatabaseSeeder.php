<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Uom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            ['password_hash' => Hash::make('admin123'), 'full_name' => 'Admin']
        );

        foreach (['Electronics', 'Clothing', 'Furniture', 'Sports', 'Home_Appliances'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
        foreach (['Samsung', 'Nike', 'Adidas', 'LG', 'IKEA'] as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }
        foreach (['Piece', 'Box', 'Kg', 'Liter', 'Dozen'] as $name) {
            Uom::firstOrCreate(['name' => $name]);
        }
    }
}