<?php

namespace App\Console\Commands;

use Database\Seeders\ContactSeeder;
use Illuminate\Console\Command;

class SeedContacts extends Command
{
    protected $signature = 'contacts:seed {count=100 : 作成するお問い合わせ件数}';

    protected $description = '既存のお問い合わせデータを削除し、指定件数のダミーデータを投入する';

    public function handle(): int
    {
        $count = (int) $this->argument('count');

        if ($count < 1) {
            $this->error('件数には1以上の整数を指定してください。');

            return self::FAILURE;
        }

        (new ContactSeeder)->run($count);

        $this->info("お問い合わせのダミーデータを{$count}件作成しました。");

        return self::SUCCESS;
    }
}
