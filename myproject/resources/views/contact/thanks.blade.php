@extends('layouts.app')

@section('title', '送信完了 | お問い合わせフォーム')

@section('content')
    <div class="text-center py-12">
        <p class="text-lg font-bold mb-4">お問い合わせありがとうございました。</p>
        <p class="text-sm text-gray-600 mb-8">内容を確認の上、担当者よりご連絡いたします。</p>
        <a href="{{ route('contact.create') }}" class="text-sm underline">トップへ戻る</a>
    </div>
@endsection
