<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '管理画面 | お問い合わせフォーム')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="max-w-4xl mx-auto px-4 py-10">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-2xl font-bold">
                <a href="{{ route('admin.contacts.index') }}">お問い合わせ管理</a>
            </h1>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm underline">ログアウト</button>
                </form>
            @endauth
        </div>

        @yield('content')
    </div>
</body>
</html>
