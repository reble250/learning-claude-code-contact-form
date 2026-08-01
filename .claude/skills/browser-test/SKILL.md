---
name: browser-test
description: Use this skill when the user wants to run this project's browser tests with Playwright and produce a screenshot-evidenced results report. Triggers on requests like "ブラウザテストを実施して", "ブラウザテスト.mdに基づいてテストして", "回帰テストして", or "スクリーンショット付きの報告書を作って" for the お問い合わせフォーム(contact form) app. Reads docs/ブラウザテスト.md for the item list and docs/ブラウザテスト報告書テンプレート.md for the report format, and writes docs/ブラウザテスト結果.md plus docs/screenshots/.
---

# ブラウザテスト実行スキル

`docs/ブラウザテスト.md`に定義された項目を実際にPlaywrightでブラウザ操作し、スクリーンショット証跡付きで`docs/ブラウザテスト結果.md`にまとめる。書式は`docs/ブラウザテスト報告書テンプレート.md`に従う。

## 前提ファイル
- `docs/ブラウザテスト.md`: テスト項目一覧（No・対応No・テスト項目・操作手順・確認ポイント）。存在しない、または対象範囲が古い場合は、先に`docs/テストケース.md`と現在の実装（`routes/web.php`、`resources/views/**`）を読んで最新化してから実行する。
- `docs/ブラウザテスト報告書テンプレート.md`: 結果ドキュメントの書式（表紙情報／概要表／サマリー表／項目ごとの操作手順・期待結果・実施結果・スクリーンショット・備考／不具合一覧／総括／添付資料）。
- `.env`の`ADMIN_EMAIL`/`ADMIN_PASSWORD`: 管理画面ログインに使う。

引数でNo（例: `BF01,BF02`や`BA`プレフィックス）が指定された場合は、該当項目のみ再実行し、結果ドキュメントの該当セクションだけ更新する。指定がなければ全項目を実行する。

## 実行手順

1. **環境確認**
   - `php artisan serve`が起動しているか確認する（`curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/contact`等）。起動していなければバックグラウンドで起動する。
   - DBにシードデータ（`database/seeders/ContactSeeder.php`等）が投入されているか`php artisan tinker`で件数を確認する。

2. **Playwrightツールのロード**
   - `mcp__playwright__browser_*`系はdeferred toolのため、`ToolSearch(select:mcp__playwright__browser_navigate,...)`で必要なツールのスキーマを先に読み込む。

3. **進捗管理**
   - 項目数が多い（10件超）場合は`TaskCreate`でセクション単位（例: フォーム入力側／管理側／境界値／その他機能）にタスクを分割し、実施ごとに`TaskUpdate`で状態を更新する。

4. **各項目の実施フロー**
   - `docs/ブラウザテスト.md`の「操作手順」に従って`browser_navigate`／`browser_fill_form`／`browser_click`／`browser_select_option`等で操作する。
   - 期待結果を`browser_snapshot`のページ内容やURL、`browser_evaluate`でのDOM確認、あるいはBashでのDB直接確認（`php artisan tinker`）で裏取りする。画面の見た目だけでなく、裏側の事実（DB値・エスケープ結果など）も確認すること。
   - 各項目につき1枚以上、`browser_take_screenshot(fullPage: true, filename: "{No}_{短い説明}.png")`でスクリーンショットを取得する。ファイル名は`docs/ブラウザテスト.md`のNoに揃える。
   - **スクリーンショットの保存先**: `filename`に相対パスを渡しても、Playwright MCPサーバーの実行時カレントディレクトリ（プロジェクトルート直下など）に保存されることがある。全項目の実行後、`find . -iname "{接頭辞}*.png" -newer <セッション開始時刻の目印ファイル>`等で洗い出し、`docs/screenshots/`へ`mv`でまとめて整理する。

5. **ブラウザの標準制約を回避する必要がある項目**（例: `<input type="date">`への不正な文字列、`<select>`にない値でのステータス更新）
   - 通常のUI操作では入力できないため、`browser_evaluate`でDOMを直接書き換える（`input.type = 'text'`にしてから値をセット、`<option>`を動的追加してから`select`する、等）。これは開発者ツールでの操作に相当する代替手段である旨を結果ドキュメントの備考に明記する。

6. **ダウンロードを伴う項目**（CSV等）
   - `browser_click`でダウンロードをトリガーすると、ツールがローカルにファイルを保存する。`file://`プロトコルへの`browser_navigate`はセキュリティ上ブロックされるため、ダウンロードファイルの中身はブラウザで開かず、Bash（`wc -l`／`head`／`cut`等）で検証する。
   - 見た目の証跡が欲しい場合は、検証したテキストを`browser_evaluate`で現在のページDOMに一時的なオーバーレイとして注入し、それをスクリーンショットする（ページの実データは書き換えない、確認用のdivを追加するだけ）。

7. **副作用の記録**
   - テスト操作自体が新規レコード作成やステータス変更などDBに副作用を与える場合、それによって既存のテストケース文書（`docs/テストケース.md`等）の想定件数と結果が変わることがある。原因を結果ドキュメントの備考に明記し、機能自体が正しいかどうかで判定する。
   - テスト用に作成したレコードの削除など破壊的なDB操作は、ユーザーに確認を取ってから行う（サンドボックスの権限で自動ブロックされる場合がある）。無理に回避しようとせず、状況を説明してユーザーに判断を委ねる。

8. **結果ドキュメントの作成**
   - `docs/ブラウザテスト報告書テンプレート.md`をコピーし、`docs/ブラウザテスト結果.md`として、以下を埋める。
     - 表紙情報（実施日・実施者・実施環境・テストURL）
     - テスト結果サマリー（分類ごとの件数・OK・NG）
     - 項目ごとの詳細（操作手順／期待結果／実施結果／スクリーンショットへの相対パス／備考）
     - 不具合一覧（NGがあれば記載。無ければ「該当なし」＋改善余地があれば申し送り事項として記載）
     - 総括（実施件数・OK率・総合判定・所見）
   - スクリーンショットは`docs/screenshots/{No}_{説明}.png`への相対パスでリンクする。

9. **完了報告**
   - 実施件数・OK/NG件数のサマリーをユーザーに簡潔に報告する。
   - 未対応の副作用（残存テストレコードなど）があれば明示し、削除してよいか確認する。
   - git commitは明示的に依頼された場合のみ行う。
