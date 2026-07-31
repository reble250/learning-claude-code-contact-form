@extends('layouts.admin')

@section('title', 'お問い合わせ一覧 | 管理画面')

@section('content')
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
                            お問い合わせはまだありません。
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
