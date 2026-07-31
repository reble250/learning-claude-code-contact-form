<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'お問い合わせフォーム')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold mb-8">
            <a href="{{ route('contact.create') }}">お問い合わせフォーム</a>
        </h1>

        @yield('content')
    </div>
</body>
</html>
