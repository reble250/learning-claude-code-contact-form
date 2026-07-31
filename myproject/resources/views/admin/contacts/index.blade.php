@extends('layouts.admin')

@section('title', 'お問い合わせ一覧 | 管理画面')

@section('content')
    <form method="GET" action="{{ route('admin.contacts.index') }}"
          class="mb-6 space-y-4 rounded border border-gray-200 bg-white p-4">
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="name" class="block text-sm font-medium mb-1">氏名</label>
                <input type="text" name="name" id="name" value="{{ $filters['name'] ?? '' }}"
                       placeholder="部分一致で検索"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium mb-1">受付日時（从）</label>
                <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium mb-1">受付日時（至）</label>
                <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <span class="block text-sm font-medium mb-1">ステータス</span>
            <div class="flex flex-wrap gap-4">
                @foreach ($statuses as $status)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="status[]" value="{{ $status->value }}"
                               class="rounded border-gray-300"
                               @checked(in_array($status->value, $filters['status'] ?? [], true))>
                        {{ $status->label() }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="rounded bg-gray-900 px-6 py-2 text-sm text-white hover:bg-gray-700">
                検索
            </button>
            <a href="{{ route('admin.contacts.index') }}" class="text-sm underline">クリア</a>
        </div>
    </form>

    <div class="overflow-hidden rounded border border-gray-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-4 py-3">受付日時</th>
                    <th class="px-4 py-3">名前</th>
                    <th class="px-4 py-3">件名</th>
                    <th class="px-4 py-3">ステータス</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($contacts as $contact)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="block">
                                {{ $contact->created_at->format('Y-m-d H:i') }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="block">
                                {{ $contact->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="block">
                                {{ $contact->subject }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="block">
                                <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $contact->status->badgeClasses() }}">
                                    {{ $contact->status->label() }}
                                </span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            @if (array_filter($filters))
                                該当するお問い合わせが見つかりません。
                            @else
                                お問い合わせはまだありません。
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $contacts->links() }}
    </div>
@endsection
