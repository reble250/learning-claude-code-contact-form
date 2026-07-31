<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * docs/テストケース.md に基づく機能テスト。
 * メソッド名の先頭（F01, A01, B01等）はテストケース.md内のNoに対応する。
 */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常な問い合わせ入力値
     *
     * @return array<string, string>
     */
    private function validContactData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'subject' => 'お問い合わせ件名',
            'body' => 'お問い合わせ本文です。',
        ], $overrides);
    }

    // =========================================================
    // 1. フォーム入力側
    // =========================================================

    #[Test]
    public function F01_入力画面が表示される(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertOk();
        $response->assertSee('名前');
        $response->assertSee('メールアドレス');
        $response->assertSee('件名');
        $response->assertSee('本文');
    }

    #[Test]
    public function F02_全項目未入力だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), [
            'name' => '',
            'email' => '',
            'subject' => '',
            'body' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'body']);
        $response->assertSessionHasErrors([
            'name' => '名前を入力してください。',
            'email' => 'メールアドレスを入力してください。',
            'subject' => '件名を入力してください。',
            'body' => '本文を入力してください。',
        ]);
    }

    #[Test]
    public function F03_メールアドレスの形式が不正だとエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスの形式で入力してください。',
        ]);
    }

    #[Test]
    public function F04_名前が255文字を超えるとエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => str_repeat('あ', 256),
        ]));

        $response->assertSessionHasErrors([
            'name' => '名前は255文字以内で入力してください。',
        ]);
    }

    #[Test]
    public function F05_本文が2000文字を超えるとエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'body' => str_repeat('あ', 2001),
        ]));

        $response->assertSessionHasErrors([
            'body' => '本文は2000文字以内で入力してください。',
        ]);
    }

    #[Test]
    public function F06_バリデーションエラー時に入力値が保持される(): void
    {
        $this->post(route('contact.confirm'), $this->validContactData([
            'name' => '保持太郎',
            'email' => 'not-valid',
        ]));

        $response = $this->get(route('contact.create'));

        $response->assertSee('保持太郎', false);
    }

    #[Test]
    public function F07_確認画面でHTMLがエスケープされる(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'subject' => '<script>alert(1)</script>',
            'body' => '本文<b>bold</b>',
        ]));

        $response->assertOk();
        $response->assertSee('<script>alert(1)</script>'); // escape=true(デフォルト)でエスケープ済み文字列として検索
        $response->assertDontSee('<script>alert(1)</script>', false); // 生のタグとしては存在しない
    }

    #[Test]
    public function F08_確認画面に入力内容が表示される(): void
    {
        $data = $this->validContactData([
            'name' => '確認太郎',
            'email' => 'kakunin@example.com',
            'subject' => '確認用件名',
            'body' => '確認用本文です',
        ]);

        $response = $this->post(route('contact.confirm'), $data);

        $response->assertOk();
        $response->assertSee($data['name']);
        $response->assertSee($data['email']);
        $response->assertSee($data['subject']);
        $response->assertSee($data['body']);
    }

    #[Test]
    public function F09_戻るボタンで入力内容を保持したまま入力画面に戻る(): void
    {
        $data = $this->validContactData(['name' => '確認太郎']);

        $response = $this->post(route('contact.back'), $data);

        $response->assertRedirect(route('contact.create'));

        $createResponse = $this->get(route('contact.create'));
        $createResponse->assertSee('確認太郎', false);
    }

    #[Test]
    public function F10_送信するとお問い合わせが保存され完了画面へ遷移する(): void
    {
        $data = $this->validContactData(['email' => 'store@example.com']);

        $response = $this->post(route('contact.store'), $data);

        $response->assertRedirect(route('contact.thanks'));
        $this->assertDatabaseHas('contacts', [
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => 'new',
        ]);

        $contact = Contact::where('email', 'store@example.com')->firstOrFail();
        $this->assertNotNull($contact->ip_address);
        $this->assertNotNull($contact->user_agent);
    }

    #[Test]
    public function F11_完了画面を再読み込みすると入力画面へリダイレクトされる(): void
    {
        $this->post(route('contact.store'), $this->validContactData());

        $first = $this->get(route('contact.thanks'));
        $first->assertOk();
        $first->assertSee('お問い合わせありがとうございました');

        $second = $this->get(route('contact.thanks'));
        $second->assertRedirect(route('contact.create'));
    }

    #[Test]
    public function F12_未送信で完了画面に直接アクセスすると入力画面へリダイレクトされる(): void
    {
        $response = $this->get(route('contact.thanks'));

        $response->assertRedirect(route('contact.create'));
    }

    // =========================================================
    // 3. 境界値テスト（フォーム入力側）
    // =========================================================

    #[Test]
    public function B01_名前_件名_本文が上限文字数ちょうどなら通過する(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => str_repeat('あ', 255),
            'subject' => str_repeat('い', 255),
            'body' => str_repeat('う', 2000),
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    #[Test]
    public function B02_メールアドレスが上限文字数ちょうどなら通過する(): void
    {
        $localPart = str_repeat('a', 255 - strlen('@example.com'));

        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => "{$localPart}@example.com",
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    #[Test]
    public function B03_メールアドレスが上限文字数を1文字超えるとエラーになる(): void
    {
        $localPart = str_repeat('a', 256 - strlen('@example.com'));

        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => "{$localPart}@example.com",
        ]));

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスは255文字以内で入力してください。',
        ]);
    }

    #[Test]
    public function B04_名前_件名_本文が1文字でも通過する(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => 'あ',
            'subject' => 'い',
            'body' => 'う',
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    #[Test]
    public function B05_件名が上限文字数を1文字超えるとエラーになる(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'subject' => str_repeat('い', 256),
        ]));

        $response->assertSessionHasErrors([
            'subject' => '件名は255文字以内で入力してください。',
        ]);
    }

    // =========================================================
    // 2. 管理側
    // =========================================================

    #[Test]
    public function A01_未ログインでは一覧にアクセスできない(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function A02_未ログインでは詳細にアクセスできない(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('admin.contacts.show', $contact));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function A03_パスワードが誤っているとログインに失敗する(): void
    {
        $admin = User::factory()->create(['password' => 'password']);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
        $this->assertGuest();
    }

    #[Test]
    public function A04_正しい認証情報でログインできる(): void
    {
        $admin = User::factory()->create(['password' => 'password']);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function A05_一覧が新着順にページネーションされて表示される(): void
    {
        Contact::factory()->count(25)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 20 && $contacts->total() === 25);
    }

    #[Test]
    public function A06_氏名の部分一致で絞り込める(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        Contact::factory()->create(['name' => '山田花子']);
        Contact::factory()->create(['name' => '鈴木一郎']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['name' => '山田']));

        $response->assertOk();
        $response->assertSee('山田太郎');
        $response->assertSee('山田花子');
        $response->assertDontSee('鈴木一郎');
    }

    #[Test]
    public function A07_該当なしの場合にメッセージが表示される(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['name' => '存在しない名前XYZ']));

        $response->assertOk();
        $response->assertSee('該当するお問い合わせが見つかりません。');
    }

    #[Test]
    public function A08_ステータスを複数選択して絞り込める(): void
    {
        Contact::factory()->create(['name' => '新規太郎', 'status' => 'new']);
        Contact::factory()->create(['name' => '対応中太郎', 'status' => 'in_progress']);
        Contact::factory()->create(['name' => '解決済太郎', 'status' => 'resolved']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', [
            'status' => ['new', 'resolved'],
        ]));

        $response->assertOk();
        $response->assertSee('新規太郎');
        $response->assertSee('解決済太郎');
        $response->assertDontSee('対応中太郎');
    }

    #[Test]
    public function A09_受付日時の期間で絞り込める(): void
    {
        Contact::factory()->create(['name' => '範囲外太郎'])->forceFill(['created_at' => '2026-01-01 10:00:00'])->save();
        Contact::factory()->create(['name' => '範囲内太郎'])->forceFill(['created_at' => '2026-02-15 10:00:00'])->save();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', [
            'date_from' => '2026-02-01',
            'date_to' => '2026-02-28',
        ]));

        $response->assertOk();
        $response->assertSee('範囲内太郎');
        $response->assertDontSee('範囲外太郎');
    }

    #[Test]
    public function A10_検索条件を組み合わせるとAND条件になる(): void
    {
        Contact::factory()->create(['name' => '山田太郎', 'status' => 'resolved']);
        Contact::factory()->create(['name' => '山田花子', 'status' => 'new']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', [
            'name' => '山田',
            'status' => ['new'],
        ]));

        $response->assertOk();
        $response->assertSee('山田花子');
        $response->assertDontSee('山田太郎');
    }

    #[Test]
    public function A11_検索条件なしなら全件表示される(): void
    {
        Contact::factory()->count(3)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 3);
    }

    #[Test]
    public function A12_ページネーションに検索条件が引き継がれる(): void
    {
        Contact::factory()->count(25)->create(['name' => '検索対象太郎']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['name' => '検索対象']));

        $response->assertOk();
        $response->assertSee('name=%E6%A4%9C%E7%B4%A2%E5%AF%BE%E8%B1%A1&amp;page=2', false);
    }

    #[Test]
    public function A13_詳細画面に内容が表示される(): void
    {
        $contact = Contact::factory()->create([
            'name' => '詳細太郎',
            'email' => 'shousai@example.com',
            'subject' => '詳細件名',
            'body' => '詳細本文',
        ]);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.show', $contact));

        $response->assertOk();
        $response->assertSee('詳細太郎');
        $response->assertSee('shousai@example.com');
        $response->assertSee('詳細件名');
        $response->assertSee('詳細本文');
    }

    #[Test]
    public function A14_存在しないIDにアクセスすると404になる(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.show', 999999));

        $response->assertNotFound();
    }

    #[Test]
    public function A15_ステータスを更新できる(): void
    {
        $contact = Contact::factory()->create(['status' => 'new']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.contacts.update', $contact), [
            'status' => 'in_progress',
        ]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $response->assertSessionHas('status_updated', true);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'in_progress',
        ]);
    }

    #[Test]
    public function A16_不正な値でのステータス更新は拒否される(): void
    {
        $contact = Contact::factory()->create(['status' => 'new']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch(route('admin.contacts.update', $contact), [
            'status' => 'invalid_status_value',
        ]);

        $response->assertSessionHasErrors([
            'status' => '選択されたステータスは無効な値です。',
        ]);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'new',
        ]);
    }

    #[Test]
    public function A17_未ログインでのステータス更新は拒否される(): void
    {
        $contact = Contact::factory()->create(['status' => 'new']);

        $response = $this->patch(route('admin.contacts.update', $contact), [
            'status' => 'resolved',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'new',
        ]);
    }

    #[Test]
    public function A18_ログアウト後は再びアクセスが拒否される(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $this->post(route('logout'));

        $response = $this->get(route('admin.contacts.index'));
        $response->assertRedirect(route('login'));
    }

    // =========================================================
    // 3. 境界値テスト（管理側）
    // =========================================================

    #[Test]
    public function B06_ステータス未選択なら全件表示される(): void
    {
        Contact::factory()->create(['status' => 'new']);
        Contact::factory()->create(['status' => 'resolved']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['status' => []]));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 2);
    }

    #[Test]
    public function B07_不正な日付形式で検索するとエラーが表示される(): void
    {
        $admin = User::factory()->create();

        // 注: 中間レスポンスに対してassertSessionHasErrors()等のセッション検証を行うと、
        // TestResponse::session()がセッションを再startしてしまい、後続リクエストへの
        // フラッシュデータの引き継ぎが崩れるテスト環境特有の挙動があるため、
        // ここでは意図的にリダイレクト先の実際の表示内容のみで検証する。
        $this->actingAs($admin);
        $this->get(route('admin.contacts.index')); // back()の戻り先を自然に確立する

        $response = $this->get(route('admin.contacts.index', ['date_from' => 'not-a-date']));

        $response->assertRedirect(route('admin.contacts.index'));

        $followUp = $this->get(route('admin.contacts.index'));
        $followUp->assertSee('検索条件にエラーがあります。');
        $followUp->assertSee('受付日時（从）には正しい日付を指定してください。');
    }

    #[Test]
    public function B08_存在しないページ番号でもエラーにならず空表示になる(): void
    {
        Contact::factory()->count(5)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['page' => 999]));

        $response->assertOk();
        $response->assertSee('お問い合わせはまだありません。');
    }

    #[Test]
    public function B16_不正なステータス更新後にエラーが詳細画面に表示される(): void
    {
        $contact = Contact::factory()->create(['status' => 'new']);
        $admin = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.contacts.update', $contact), [
            'status' => 'invalid_value',
        ]);

        $response = $this->get(route('admin.contacts.show', $contact));

        $response->assertSee('選択されたステータスは無効な値です。');
    }
}
