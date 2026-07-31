<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * docs/テストケース.md に基づく機能テスト。
 * 各テストメソッドのコメントにあるID（F01, A01, B01等）はテストケース.md内のNoに対応する。
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

    /** F01: 入力画面表示 */
    public function test_input_form_is_displayed(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertOk();
        $response->assertSee('名前');
        $response->assertSee('メールアドレス');
        $response->assertSee('件名');
        $response->assertSee('本文');
    }

    /** F02: 必須項目未入力 */
    public function test_validation_fails_when_all_fields_are_empty(): void
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

    /** F03: メールアドレス形式不正 */
    public function test_validation_fails_for_invalid_email_format(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスの形式で入力してください。',
        ]);
    }

    /** F04: 名前が255文字超過 */
    public function test_validation_fails_when_name_exceeds_max_length(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => str_repeat('あ', 256),
        ]));

        $response->assertSessionHasErrors([
            'name' => '名前は255文字以内で入力してください。',
        ]);
    }

    /** F05: 本文が2000文字超過 */
    public function test_validation_fails_when_body_exceeds_max_length(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'body' => str_repeat('あ', 2001),
        ]));

        $response->assertSessionHasErrors([
            'body' => '本文は2000文字以内で入力してください。',
        ]);
    }

    /** F06: バリデーションエラー時の入力保持 */
    public function test_old_input_is_retained_after_validation_error(): void
    {
        $this->post(route('contact.confirm'), $this->validContactData([
            'name' => '保持太郎',
            'email' => 'not-valid',
        ]));

        $response = $this->get(route('contact.create'));

        $response->assertSee('保持太郎', false);
    }

    /** F07: XSS対策（エスケープ） */
    public function test_html_is_escaped_in_confirm_page(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'subject' => '<script>alert(1)</script>',
            'body' => '本文<b>bold</b>',
        ]));

        $response->assertOk();
        $response->assertSee('<script>alert(1)</script>'); // escape=true(デフォルト)でエスケープ済み文字列として検索
        $response->assertDontSee('<script>alert(1)</script>', false); // 生のタグとしては存在しない
    }

    /** F08: 確認画面の表示内容 */
    public function test_confirm_page_displays_submitted_values(): void
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

    /** F09: 「戻る」で入力保持 */
    public function test_back_redirects_to_create_with_input_retained(): void
    {
        $data = $this->validContactData(['name' => '確認太郎']);

        $response = $this->post(route('contact.back'), $data);

        $response->assertRedirect(route('contact.create'));

        $createResponse = $this->get(route('contact.create'));
        $createResponse->assertSee('確認太郎', false);
    }

    /** F10: 送信・保存 */
    public function test_store_creates_contact_and_redirects_to_thanks(): void
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

    /** F11: 完了画面の再アクセスガード */
    public function test_thanks_page_reload_redirects_to_create(): void
    {
        $this->post(route('contact.store'), $this->validContactData());

        $first = $this->get(route('contact.thanks'));
        $first->assertOk();
        $first->assertSee('お問い合わせありがとうございました');

        $second = $this->get(route('contact.thanks'));
        $second->assertRedirect(route('contact.create'));
    }

    /** F12: 完了画面への直接アクセス */
    public function test_thanks_page_direct_access_without_submission_redirects(): void
    {
        $response = $this->get(route('contact.thanks'));

        $response->assertRedirect(route('contact.create'));
    }

    // =========================================================
    // 3. 境界値テスト（フォーム入力側）
    // =========================================================

    /** B01: name/subject/body 上限ちょうど */
    public function test_name_subject_body_at_upper_boundary_passes(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => str_repeat('あ', 255),
            'subject' => str_repeat('い', 255),
            'body' => str_repeat('う', 2000),
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    /** B02: email 上限ちょうど（255文字） */
    public function test_email_at_upper_boundary_passes(): void
    {
        $localPart = str_repeat('a', 255 - strlen('@example.com'));

        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => "{$localPart}@example.com",
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    /** B03: email 上限+1（256文字） */
    public function test_email_over_upper_boundary_fails(): void
    {
        $localPart = str_repeat('a', 256 - strlen('@example.com'));

        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'email' => "{$localPart}@example.com",
        ]));

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスは255文字以内で入力してください。',
        ]);
    }

    /** B04: name/subject/body 下限（1文字） */
    public function test_name_subject_body_at_lower_boundary_passes(): void
    {
        $response = $this->post(route('contact.confirm'), $this->validContactData([
            'name' => 'あ',
            'subject' => 'い',
            'body' => 'う',
        ]));

        $response->assertOk();
        $response->assertSessionHasNoErrors();
    }

    /** B05: subject 上限+1（256文字） */
    public function test_subject_over_upper_boundary_fails(): void
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

    /** A01: 未ログイン時の一覧アクセス制御 */
    public function test_guest_cannot_access_admin_index(): void
    {
        $response = $this->get(route('admin.contacts.index'));

        $response->assertRedirect(route('login'));
    }

    /** A02: 未ログイン時の詳細アクセス制御 */
    public function test_guest_cannot_access_admin_show(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->get(route('admin.contacts.show', $contact));

        $response->assertRedirect(route('login'));
    }

    /** A03: ログイン失敗 */
    public function test_login_fails_with_wrong_password(): void
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

    /** A04: ログイン成功 */
    public function test_login_succeeds_with_correct_credentials(): void
    {
        $admin = User::factory()->create(['password' => 'password']);

        $response = $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertAuthenticatedAs($admin);
    }

    /** A05: 一覧表示・ページネーション */
    public function test_admin_index_lists_contacts_paginated(): void
    {
        Contact::factory()->count(25)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->count() === 20 && $contacts->total() === 25);
    }

    /** A06: 氏名部分一致検索 */
    public function test_admin_index_filters_by_partial_name(): void
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

    /** A07: 該当なし検索 */
    public function test_admin_index_shows_no_results_message(): void
    {
        Contact::factory()->create(['name' => '山田太郎']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['name' => '存在しない名前XYZ']));

        $response->assertOk();
        $response->assertSee('該当するお問い合わせが見つかりません。');
    }

    /** A08: ステータス複数選択検索 */
    public function test_admin_index_filters_by_multiple_statuses(): void
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

    /** A09: 受付日時期間検索 */
    public function test_admin_index_filters_by_date_range(): void
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

    /** A10: 検索条件の組み合わせ（AND） */
    public function test_admin_index_combined_filters_use_and_condition(): void
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

    /** A11: 検索条件クリア */
    public function test_admin_index_without_filters_shows_all(): void
    {
        Contact::factory()->count(3)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 3);
    }

    /** A12: ページネーションへの検索条件引き継ぎ */
    public function test_admin_index_pagination_retains_query_string(): void
    {
        Contact::factory()->count(25)->create(['name' => '検索対象太郎']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['name' => '検索対象']));

        $response->assertOk();
        $response->assertSee('name=%E6%A4%9C%E7%B4%A2%E5%AF%BE%E8%B1%A1&amp;page=2', false);
    }

    /** A13: 詳細画面の表示内容 */
    public function test_admin_show_displays_contact_details(): void
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

    /** A14: 存在しないIDへのアクセス */
    public function test_admin_show_404_for_nonexistent_contact(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.show', 999999));

        $response->assertNotFound();
    }

    /** A15: ステータス更新（正常系） */
    public function test_admin_can_update_status(): void
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

    /** A16: ステータス更新（異常系：不正な値） */
    public function test_admin_status_update_rejects_invalid_value(): void
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

    /** A17: 未ログインでのステータス更新は拒否される */
    public function test_unauthenticated_status_update_is_blocked(): void
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

    /** A18: ログアウト後の再アクセス制御 */
    public function test_admin_index_is_blocked_again_after_logout(): void
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

    /** B06: ステータス検索0件選択 */
    public function test_admin_index_with_no_status_selected_shows_all(): void
    {
        Contact::factory()->create(['status' => 'new']);
        Contact::factory()->create(['status' => 'resolved']);
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['status' => []]));

        $response->assertOk();
        $response->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 2);
    }

    /** B07: 検索フォームに不正な日付形式（修正済み: 画面にエラーを表示する） */
    public function test_admin_index_with_invalid_date_format_shows_error(): void
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

    /** B08: 存在しないページ番号 */
    public function test_admin_index_with_out_of_range_page_returns_empty_without_error(): void
    {
        Contact::factory()->count(5)->create();
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index', ['page' => 999]));

        $response->assertOk();
        $response->assertSee('お問い合わせはまだありません。');
    }

    /** B16: ステータス更新に不正な値（修正済み: 詳細画面にエラーを表示する） */
    public function test_admin_show_displays_error_after_invalid_status_update(): void
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
