## claude cone install
curl -fsSL https://claude.ai/install.sh | bash

## php, composer, laravel install
sudo apt update
sudo apt install -y php-cli php-mbstring php-xml php-curl php-zip php-sqlite3 sqlite3

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

composer global require laravel/installer

php -v
compoer --version

## Larabel prj create
composer create-project laravel/laravel myproject
cd myproject
php artisan --version

## Laravel 初期ページ確認
php artisan serve
http://127.0.0.1:8000

## 
CLAUDE.mdに用件を記載

問い合わせフォームを作りたいと思います。データベースに保存する項目として、何が必要か提案してください 



