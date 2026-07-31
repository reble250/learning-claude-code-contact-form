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

## アプリ作成プロンプト
CLAUDE.mdに用件を記載

問い合わせフォームを作りたいと思います。データベースに保存する項目として、何が必要か提案してください 

管理者ページに検索機能をつけて。「氏名（部分一致）」「日時」「ステータス（複数選択可）」で絞りこめるようにしt

## テストプロンプト
お問い合わせデータを100件、DBに投入するシーダーを作成してください
- 名前は日本語の声明でバリエーション豊かに男女比半々
- メールはexample.comドメイン
- 件名と本文は、ショッピングサイトを想定した、よくありがちなもの
- ステータスは「新規」「対応中」「解決済み」をランダム
- 日付は過去三ヶ月の範囲でランダム

シーダーを実行してテストデータを投入して

お問い合わせフォームのテストケースを考えてください
「フォームの入力側」と「管理側」のそれぞれについて、テストすべき項目を一覧にし、そのテスト結果を「テストケース.md」というファイルにまとめてください

テストケース.mdの内容を内容を人間が手作業で確認したいので、○×がつけられるようなExcelシートに変換して欲しい。
