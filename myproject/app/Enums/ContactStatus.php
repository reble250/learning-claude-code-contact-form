<?php

namespace App\Enums;

/**
 * お問い合わせの対応ステータス
 */
enum ContactStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    /**
     * 管理画面表示用の日本語ラベルを返す
     */
    public function label(): string
    {
        return match ($this) {
            self::New => '新規',
            self::InProgress => '対応中',
            self::Resolved => '解決済み',
        };
    }

    /**
     * 管理画面のバッジ表示用Tailwindクラスを返す
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-800',
            self::InProgress => 'bg-yellow-100 text-yellow-800',
            self::Resolved => 'bg-green-100 text-green-800',
        };
    }
}
