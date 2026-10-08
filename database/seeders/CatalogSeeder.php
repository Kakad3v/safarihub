<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Safari', 'Beach', 'Islands', 'Hills', 'Culture', 'Food'] as $i => $name) {
            Category::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i]);
        }

        foreach (['Masai Mara', 'Diani', 'Wasini Island', 'Mpunguti', 'Chullu Hills'] as $name) {
            Destination::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
