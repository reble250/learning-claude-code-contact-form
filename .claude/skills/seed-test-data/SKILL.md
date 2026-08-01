---
name: seed-test-data
description: Use this skill when the user wants to (re)generate contact form dummy/test data with a specific record count, e.g. "テストデータをN件作成して", "ダミーデータを100件投入して", "テストデータを作り直して". Runs `php artisan contacts:seed {count}`, which deletes existing Contact records and creates exactly {count} new ones (roughly even male/female split, random status/date within the past 3 months).
---

# テストデータ作成スキル

`php artisan contacts:seed {count}`を実行して、お問い合わせのダミーデータを指定件数だけ作り直す。

## 実装
- `database/seeders/ContactSeeder.php`: `run(int $count = 100)`。**既存のContactレコードを`truncate()`で全削除してから**、`$count`件を作成する（男女比はできるだけ半々、奇数の場合は男性側が1件少ない）。
- `app/Console/Commands/SeedContacts.php`: `contacts:seed {count=100}`というArtisanコマンド。引数`count`を受け取り`ContactSeeder`を直接呼び出す。1未満の値はエラーにして終了する。
- 管理者ユーザー（`AdminUserSeeder`）はこのコマンドの対象外で、影響を受けない。

## 実行手順
1. ユーザーの指示から件数を決める。
   - 明示的に件数の指定があればそれを使う（例:「50件作成して」→50）。
   - 指定がなければデフォルトの100件でよいか確認するか、100件で実行してその旨を報告する。
2. `php artisan contacts:seed {count}`を実行する。
3. **既存データは毎回全削除される**ため、実行前に「既存のお問い合わせデータを削除して作り直します」と一言断ってから実行する。既存データを消してほしくない状況（他のテスト作業中など）が疑われる場合は、実行前にユーザーに確認する。
4. 実行後、`php artisan tinker --execute="echo App\Models\Contact::count();"`等で件数を確認し、結果をユーザーに簡潔に報告する。

## 注意
- このコマンドは`Contact::truncate()`を伴う破壊的操作。既存の検証済みデータ（手動で作成したレコードなど）が消える点を毎回明示すること。
- 件数はintキャストされるだけなので、極端に大きい値（数万件など）を指定された場合は実行前に処理時間の見積もりをユーザーに伝える。
