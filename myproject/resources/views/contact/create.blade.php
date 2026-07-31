@extends('layouts.app')

@section('title', 'お問い合わせ | お問い合わせフォーム')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-bold mb-2">入力内容にエラーがあります。</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('contact.confirm') }}" class="space-y-6">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium mb-1">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="w-full rounded border border-gray-300 px-3 py-2">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-1">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full rounded border border-gray-300 px-3 py-2">
        </div>

        <div>
            <label for="subject" class="block text-sm font-medium mb-1">件名</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                   class="w-full rounded border border-gray-300 px-3 py-2">
        </div>

        <div>
            <label for="body" class="block text-sm font-medium mb-1">本文</label>
            <textarea name="body" id="body" rows="8"
                      class="w-full rounded border border-gray-300 px-3 py-2">{{ old('body') }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="rounded bg-gray-900 px-6 py-2 text-white hover:bg-gray-700">
                確認画面へ
            </button>
        </div>
    </form>
@endsection
