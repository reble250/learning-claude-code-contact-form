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

テストケース.mdに基づいて、機能テストのコードを出力してください。ファイル名はContactFormTest.php

実行して

## TDD prompt
これからCSVエクスポートをつくる。TDDで進めたいので、テストのみを書いて。実装はしないで。
テストはtests/Feature/CSVExportTest.phpとして。

テストケースは以下の通り。
- GET /admin/Contacts/exportでCSVファイルがダウンロードされること。
- 管理者しか使用できないこと
- CSVにはヘッダー行(ID、名前、メールアドレス、件名、ステータス、受信日時)が含まれること
- 引数に「ステータス」を指定することができ、そのステータスで絞り込まれる。例えば、「&status=ステータス」
- 文字コードはUTF-8のBOMつき
- DBのレコードがCSVに含まれる

テストが通るようにCSVエクスポート機能を実装してください


// MCP
sudo apt update
sudo apt install -y nodejs npm
claude mcp add playwright -- npx @playwright/mcp@latest

claude
/mcp

Playwright MCPを使って。https://book.mynavi.jp/ を開いて内容を教えてください

ヘッドレスのchromeを使ってみて

ブラウザのスクリーンショットをscreen001,pngとして出力して

今のスクリーンショットは日本語が化けているようです。日本語フォントをインストールするなどして、化けないようにしてください

PlayWright MCPを使って、http://127.0.0.1:8006/contactを開いてください。フォームには以下の内容を入力して送信してください。
- 名前：テスト太郎
- メールアドレス：test@example.com
- 件名：testお問合せ
- 本文：これは MCPテストです。自動入力です。

入力した画面と入力後の遷移した画面それぞれでスクリーンショットをとってください


/clear

ブラウザテストをしたいと考えています。それに先立ち、「テストケース.md」をもとに、ブラウザテストの項目を考え、「ブラウザテスト.md」に出力してください。
このブラウザテストは、スクリーンショットによる証跡をつけて先方へ提出する予定です。そのための雛形を「ブラウザテスト報告書テンプレート.md」として、まとめてください


ブラウザテスト.mdに基づくテストをplaywrightで実施し、その結果を、「ブラウザテスト結果.md」にまとめて欲しい。
書き方の雛形は、ブラウザテスト報告書テンプレート.mdに従うこと。