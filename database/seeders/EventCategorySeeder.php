<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'wedding', 'name_en' => 'Wedding', 'name_sw' => 'Harusi', 'sort_order' => 1],
            ['slug' => 'sendoff', 'name_en' => 'Sendoff', 'name_sw' => 'Kuaga', 'sort_order' => 2],
            ['slug' => 'kitchen_party', 'name_en' => 'Kitchen Party', 'name_sw' => 'Kitchen Party', 'sort_order' => 3],
            ['slug' => 'birthday', 'name_en' => 'Birthday', 'name_sw' => 'Siku ya Kuzaliwa', 'sort_order' => 4],
            ['slug' => 'graduation', 'name_en' => 'Graduation', 'name_sw' => 'Kuhitimu', 'sort_order' => 5],
            ['slug' => 'anniversary', 'name_en' => 'Anniversary', 'name_sw' => 'Kumbukumbu', 'sort_order' => 6],
            ['slug' => 'engagement', 'name_en' => 'Engagement', 'name_sw' => 'Uguano', 'sort_order' => 7],
            ['slug' => 'corporate', 'name_en' => 'Corporate Event', 'name_sw' => 'Tukio la Kampuni', 'sort_order' => 8],
            ['slug' => 'other', 'name_en' => 'Other Celebration', 'name_sw' => 'Sherehe Nyingine', 'sort_order' => 9],
        ];

        foreach ($categories as $category) {
            EventCategory::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
