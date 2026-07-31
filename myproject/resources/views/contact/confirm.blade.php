@extends('layouts.app')

@section('title', '確認 | お問い合わせフォーム')

@section('content')
    <p class="mb-6 text-sm text-gray-600">以下の内容でよろしければ「送信する」を押してください。</p>

    <dl class="space-y-6 mb-8">
        <div>
            <dt class="text-sm font-medium text-gray-500">名前</dt>
            <dd class="mt-1 whitespace-pre-wrap">{{ $input['name'] }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
            <dd class="mt-1 whitespace-pre-wrap">{{ $input['email'] }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">件名</dt>
            <dd class="mt-1 whitespace-pre-wrap">{{ $input['subject'] }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">本文</dt>
            <dd class="mt-1 whitespace-pre-wrap">{{ $input['body'] }}</dd>
        </div>
    </dl>

    <form method="POST" action="{{ route('contact.store') }}" class="flex justify-between">
        @csrf
        <input type="hidden" name="name" value="{{ $input['name'] }}">
        <input type="hidden" name="email" value="{{ $input['email'] }}">
        <input type="hidden" name="subject" value="{{ $input['subject'] }}">
        <input type="hidden" name="body" value="{{ $input['body'] }}">

        <button type="submit" formaction="{{ route('contact.back') }}"
                class="rounded border border-gray-300 px-6 py-2 hover:bg-gray-100">
            戻る
        </button>
        <button type="submit" formaction="{{ route('contact.store') }}"
                class="rounded bg-gray-900 px-6 py-2 text-white hover:bg-gray-700">
            送信する
        </button>
    </form>
@endsection
