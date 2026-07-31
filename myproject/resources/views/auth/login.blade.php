@extends('layouts.admin')

@section('title', 'ログイン | 管理画面')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="max-w-sm space-y-6 rounded border border-gray-200 bg-white p-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium mb-1">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" autofocus
                   class="w-full rounded border border-gray-300 px-3 py-2">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">パスワード</label>
            <input type="password" name="password" id="password"
                   class="w-full rounded border border-gray-300 px-3 py-2">
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
            <label for="remember" class="text-sm">ログイン状態を保持する</label>
        </div>

        <div class="text-right">
            <button type="submit" class="rounded bg-gray-900 px-6 py-2 text-white hover:bg-gray-700">
                ログイン
            </button>
        </div>
    </form>
@endsection
