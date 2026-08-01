<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * 既存のお問い合わせデータを削除したうえで、指定件数のダミーデータを男女比半々で作成する
     */
    public function run(int $count = 100): void
    {
        Contact::truncate();

        $male = intdiv($count, 2);
        $female = $count - $male;

        Contact::factory()->count($male)->male()->create();
        Contact::factory()->count($female)->female()->create();
    }
}
