<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Gym;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::where('slug', 'jack-gym')->first() ?? Gym::first();

        $categories = ['Rent', 'Electricity', 'Water', 'Equipment', 'Maintenance', 'Salary', 'Marketing', 'Software', 'Cleaning', 'Supplies', 'Miscellaneous'];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['gym_id' => $gym?->id, 'name' => $category],
                ['description' => $category . ' expenses']
            );
        }
    }
}
