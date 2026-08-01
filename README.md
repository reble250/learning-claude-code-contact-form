# learning-claude-code-contact-form

書籍『Claude Code実用入門』6〜9章の学習成果物です。「お問い合わせフォーム」（Laravel/SQLite）を題材に、devcontainerによる環境構築からアプリ開発、テスト、MCPサーバー活用、スキル・カスタムサブエージェントによる効率化までを一通り実践しています。

アプリ本体は[`myproject/`](./myproject)配下（Laravelプロジェクト）。

## 学習観点とリポジトリ内容の対応

### 1. DB・管理機能・認証を含んだアプリ開発

お問い合わせフォームの本体機能。

| 要素 | 内容 |
|---|---|
| DB設計 | [`database/migrations/..._create_contacts_table.php`](./myproject/database/migrations)、[`app/Models/Contact.php`](./myproject/app/Models/Contact.php)、[`app/Enums/ContactStatus.php`](./myproject/app/Enums/ContactStatus.php)（新規／対応中／解決済み） |
| ユーザー向けフォーム | [`app/Http/Controllers/ContactController.php`](./myproject/app/Http/Controllers/ContactController.php)、[`app/Http/Requests/ContactRequest.php`](./myproject/app/Http/Requests/ContactRequest.php)、`resources/views/contact/{create,confirm,thanks}.blade.php`（入力→確認→完了の3画面フロー） |
| 管理機能 | [`app/Http/Controllers/Admin/ContactController.php`](./myproject/app/Http/Controllers/Admin/ContactController.php)、`resources/views/admin/contacts/{index,show}.blade.php`（一覧・詳細・ステータス変更、検索/絞り込み、CSVダウンロード） |
| 認証 | [`app/Http/Controllers/Auth/LoginController.php`](./myproject/app/Http/Controllers/Auth/LoginController.php)、`resources/views/auth/login.blade.php`、[`database/seeders/AdminUserSeeder.php`](./myproject/database/seeders/AdminUserSeeder.php)（管理画面へのログイン制御） |
| 仕様 | [`docs/仕様書.md`](./myproject/docs/仕様書.md) |

### 2. テスト・テストデータの作成・実施

TDDと検証データ整備。

| 要素 | 内容 |
|---|---|
| 仕様→テストケース | [`docs/テストケース.md`](./myproject/docs/テストケース.md)（境界値・異常系込み） |
| 自動テスト | [`tests/Feature/ContactFormTest.php`](./myproject/tests/Feature/ContactFormTest.php)、[`tests/Feature/CSVExportTest.php`](./myproject/tests/Feature/CSVExportTest.php)（CSV機能はTDDのred→green） |
| テストデータ生成 | [`database/factories/ContactFactory.php`](./myproject/database/factories/ContactFactory.php)、[`database/seeders/ContactSeeder.php`](./myproject/database/seeders/ContactSeeder.php)（男女比半々の疑似データ） |
| テストデータ作成の省力化 | [`app/Console/Commands/SeedContacts.php`](./myproject/app/Console/Commands/SeedContacts.php) ＋ [`.claude/skills/seed-test-data/`](./.claude/skills/seed-test-data)（件数を指定して`contacts:seed`一発で作り直す） |

### 3. MCPサーバーの学習（Playwright MCP）

実ブラウザ操作によるE2E検証。

| 要素 | 内容 |
|---|---|
| テスト項目定義 | [`docs/ブラウザテスト.md`](./myproject/docs/ブラウザテスト.md) |
| 報告書の型 | [`docs/ブラウザテスト報告書テンプレート.md`](./myproject/docs/ブラウザテスト報告書テンプレート.md) |
| 実施結果 | [`docs/ブラウザテスト結果.md`](./myproject/docs/ブラウザテスト結果.md) ＋ [`docs/screenshots/`](./myproject/docs/screenshots)（BF/BA/BB/BE系、40枚超のスクリーンショット証跡） |
| 恒常化 | [`.claude/skills/browser-test/`](./.claude/skills/browser-test)（Playwright MCPツールでの実施手順をスキル化） |

### 4. スキルとカスタムエージェントによる効率化・省力化

Claude Codeの拡張機能そのものの学習。

| 要素 | 内容 |
|---|---|
| カスタムスキル | [`.claude/skills/browser-test/SKILL.md`](./.claude/skills/browser-test/SKILL.md)（ブラウザテスト実施の定型化）、[`.claude/skills/seed-test-data/SKILL.md`](./.claude/skills/seed-test-data/SKILL.md)（テストデータ作成の定型化） |
| カスタムサブエージェント | [`.claude/agents/cre-reviewer.md`](./.claude/agents/cre-reviewer.md)（`core-reviewer`：読み取り専用でセキュリティ／パフォーマンス／可読性／ベストプラクティスをレビュー） |
| 試行錯誤の記録 | [`Claude Code学習メモ.md`](./Claude%20Code学習メモ.md)（ブラウザテスト自動化編）、[`Claude Code学習メモ_スキル編.md`](./Claude%20Code学習メモ_スキル編.md)（スキルの検出タイミングなど仕様調査の過程） |

### 5. devcontainerによる開発環境構築

Codespaces/VS Codeで即座に同じ環境を再現するための構成。

| 要素 | 内容 |
|---|---|
| 環境定義 | [`.devcontainer/devcontainer.json`](./.devcontainer/devcontainer.json)（Ubuntuベースイメージ＋`php`Feature（8.3・Composer込み）で構築） |
| 追加セットアップ | `postCreateCommand`で`php-mbstring`/`php-xml`/`php-curl`/`php-zip`/`php-sqlite3`等の拡張とComposer経由のlaravel installerを導入 |

## セットアップ

```bash
cd myproject
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
