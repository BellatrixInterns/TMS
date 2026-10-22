<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Uom;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
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
