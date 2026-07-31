<?php

namespace Database\Factories;

use App\Enums\ContactStatus;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @var array<int, string>
     */
    private array $surnames = [
        '佐藤', '鈴木', '高橋', '田中', '伊藤', '渡辺', '山本', '中村', '小林', '加藤',
        '吉田', '山田', '佐々木', '山口', '松本', '井上', '木村', '林', '斎藤', '清水',
    ];

    /**
     * @var array<int, string>
     */
    private array $maleGivenNames = [
        '翔太', '大輔', '拓也', '健太', '直樹', '亮', '誠', '修', '学', '剛',
        '洋平', '悠斗', '陽介', '和也', '慎太郎', '拓海', '涼', '隼人', '篤', '雄大',
    ];

    /**
     * @var array<int, string>
     */
    private array $femaleGivenNames = [
        '美咲', '由美', '愛', '陽子', '恵', '真由美', '香織', '麻衣', 'さくら', '千尋',
        '直美', '智子', '裕子', '由紀', '彩', '綾', '舞', '結衣', '花', '美穂',
    ];

    /**
     * ショッピングサイトを想定した、よくある問い合わせの件名・本文パターン
     *
     * @var array<int, array{subject: string, body: string}>
     */
    private array $inquiryTemplates = [
        ['subject' => '注文した商品が届きません', 'body' => "先日注文した商品がまだ届いていません。\n注文番号: %s\n発送状況をご確認いただけますでしょうか。"],
        ['subject' => '商品を返品したい', 'body' => "購入した商品のサイズが合わなかったため、返品を希望します。\n注文番号: %s\n返品の手続き方法を教えてください。"],
        ['subject' => '届いた商品が写真と違う', 'body' => "注文した商品が、サイト掲載の写真と色味が異なるように見えます。\n注文番号: %s\n交換は可能でしょうか。"],
        ['subject' => '支払い方法について', 'body' => "注文時にクレジットカード決済が失敗してしまいます。\n他の支払い方法に変更することは可能でしょうか。"],
        ['subject' => '配送日時の変更をお願いしたい', 'body' => "注文番号 %s の商品について、配送希望日を変更したいです。\nご対応いただけますでしょうか。"],
        ['subject' => '注文をキャンセルしたい', 'body' => "先ほど注文した商品をキャンセルしたいです。\n注文番号: %s\nお手数ですがご対応をお願いいたします。"],
        ['subject' => 'サイズ交換について', 'body' => "購入したサイズが小さかったため、ワンサイズ大きいものに交換したいです。\n注文番号: %s\n可能かどうか教えてください。"],
        ['subject' => 'クーポンコードが使えない', 'body' => "サイトで配布されていたクーポンコードを入力しましたが、エラーが表示され利用できませんでした。\nご確認をお願いいたします。"],
        ['subject' => '領収書の発行をお願いしたい', 'body' => "注文番号 %s の商品について、宛名入りの領収書を発行していただけますでしょうか。"],
        ['subject' => '会員登録ができない', 'body' => "会員登録画面で確認メールが届かず、登録が完了しません。\nメールアドレスの再確認をお願いできますでしょうか。"],
        ['subject' => '在庫の再入荷について', 'body' => "現在売り切れになっている商品の再入荷予定はありますでしょうか。\n入荷の目安が分かれば教えてください。"],
        ['subject' => 'ギフトラッピングについて', 'body' => "プレゼント用にラッピングをお願いしたいのですが、注文後の追加は可能でしょうか。\n注文番号: %s"],
        ['subject' => '送料についての質問', 'body' => "地域によって送料が異なるようですが、詳しい料金表を確認する方法を教えてください。"],
        ['subject' => 'パスワードを再設定できない', 'body' => "パスワード再設定用のメールが届かず、ログインできない状態です。\nご確認をお願いいたします。"],
        ['subject' => '破損した商品が届いた', 'body' => "届いた商品の箱が潰れており、中身が破損していました。\n注文番号: %s\n交換をお願いできますでしょうか。"],
        ['subject' => 'マイページの注文履歴が表示されない', 'body' => "マイページにログインしても、過去の注文履歴が一件も表示されません。\n不具合の可能性はありますでしょうか。"],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $template = fake()->randomElement($this->inquiryTemplates);
        $orderNumber = 'ORD-'.fake()->unique()->numerify('######');
        $createdAt = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'name' => $this->randomName(fake()->boolean() ? 'male' : 'female'),
            'email' => fake()->unique()->userName().'@example.com',
            'subject' => $template['subject'],
            'body' => str_contains($template['body'], '%s')
                ? sprintf($template['body'], $orderNumber)
                : $template['body'],
            'status' => fake()->randomElement(ContactStatus::cases())->value,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    /**
     * 男性名で生成
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->randomName('male'),
        ]);
    }

    /**
     * 女性名で生成
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->randomName('female'),
        ]);
    }

    /**
     * 姓と名（性別別）を組み合わせたランダムな氏名を生成
     */
    private function randomName(string $gender): string
    {
        $surname = fake()->randomElement($this->surnames);
        $givenName = fake()->randomElement($gender === 'male' ? $this->maleGivenNames : $this->femaleGivenNames);

        return "{$surname} {$givenName}";
    }
}
