@extends('layouts.admin')

@section('title', 'お問い合わせ詳細 | 管理画面')

@section('content')
    <a href="{{ route('admin.contacts.index') }}" class="mb-6 inline-block text-sm underline">&larr; 一覧へ戻る</a>

    @if (session('status_updated'))
        <div class="mb-6 rounded border border-green-300 bg-green-50 p-4 text-sm text-green-700">
            ステータスを更新しました。
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded border border-red-300 bg-red-50 p-4 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="rounded border border-gray-200 bg-white p-6">
        <dl class="space-y-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">受付日時</dt>
                <dd class="mt-1">{{ $contact->created_at->format('Y-m-d H:i') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">名前</dt>
                <dd class="mt-1">{{ $contact->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                <dd class="mt-1">{{ $contact->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">件名</dt>
                <dd class="mt-1">{{ $contact->subject }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">本文</dt>
                <dd class="mt-1 whitespace-pre-wrap">{{ $contact->body }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="mt-8 flex items-center gap-3">
            @csrf
            @method('PATCH')

            <label for="status" class="text-sm font-medium text-gray-500">ステータス</label>
            <select name="status" id="status" class="rounded border border-gray-300 px-3 py-2 text-sm">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($contact->status === $status)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                更新する
            </button>
        </form>
    </div>
@endsection
