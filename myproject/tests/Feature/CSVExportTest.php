<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * 管理画面のお問い合わせCSVエクスポート機能のテスト（TDD: 実装前）。
 * 対象エンドポイント: GET /admin/contacts/export
 */
class CSVExportTest extends TestCase
{
    use RefreshDatabase;

    private const EXPORT_URL = '/admin/contacts/export';

    /**
     * レスポンス本文からBOMを取り除き、CSVの行配列（各行はカラム配列）を返す
     *
     * @return array<int, array<int, string>>
     */
    private function parseCsv(string $content): array
    {
        $withoutBom = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $lines = array_filter(explode("\n", str_replace("\r\n", "\n", trim($withoutBom))));

        return array_map(fn (string $line) => str_getcsv($line), $lines);
    }

    #[Test]
    public function CSVファイルがダウンロードされる(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(self::EXPORT_URL);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringContainsString(
            '.csv',
            $response->headers->get('Content-Disposition')
        );
    }

    #[Test]
    public function 未ログインの場合はエクスポートできない(): void
    {
        $response = $this->get(self::EXPORT_URL);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function CSVにヘッダー行が含まれる(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(self::EXPORT_URL);

        $rows = $this->parseCsv($response->streamedContent());

        $this->assertSame(
            ['ID', '名前', 'メールアドレス', '件名', 'ステータス', '受信日時'],
            $rows[0]
        );
    }

    #[Test]
    public function statusパラメータで指定したステータスのみに絞り込まれる(): void
    {
        $admin = User::factory()->create();
        $newContact = Contact::factory()->create([
            'name' => '新規太郎',
            'email' => 'shinki@example.com',
            'status' => 'new',
        ]);
        $resolvedContact = Contact::factory()->create([
            'name' => '解決花子',
            'email' => 'kaiketsu@example.com',
            'status' => 'resolved',
        ]);

        $response = $this->actingAs($admin)->get(self::EXPORT_URL.'?status=new');

        $content = $response->streamedContent();

        $this->assertStringContainsString($newContact->email, $content);
        $this->assertStringNotContainsString($resolvedContact->email, $content);
    }

    #[Test]
    public function 文字コードはBOM付きUTF_8である(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(self::EXPORT_URL);

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }

    #[Test]
    public function DBのレコードがCSVに含まれる(): void
    {
        $admin = User::factory()->create();
        $contact = Contact::factory()->create([
            'name' => 'CSV太郎',
            'email' => 'csv-taro@example.com',
            'subject' => 'CSV出力確認用件名',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($admin)->get(self::EXPORT_URL);

        $rows = $this->parseCsv($response->streamedContent());
        $dataRows = array_slice($rows, 1);

        $matchingRow = collect($dataRows)->first(fn (array $row) => $row[0] === (string) $contact->id);

        $this->assertNotNull($matchingRow, 'エクスポートされたCSVに対象のレコードが見つからない');
        $this->assertSame((string) $contact->id, $matchingRow[0]);
        $this->assertSame($contact->name, $matchingRow[1]);
        $this->assertSame($contact->email, $matchingRow[2]);
        $this->assertSame($contact->subject, $matchingRow[3]);
        $this->assertSame(ContactStatus::InProgress->label(), $matchingRow[4]);
        $this->assertSame($contact->created_at->format('Y-m-d H:i:s'), $matchingRow[5]);
    }
}
