<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * お問い合わせのダミーデータを100件作成する（男女比半々）
     */
    public function run(): void
    {
        Contact::factory()->count(50)->male()->create();
        Contact::factory()->count(50)->female()->create();
    }
}
