<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧を表示（氏名・受付日時・ステータスで絞り込み可能）
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'status' => ['nullable', 'array'],
            'status.*' => [Rule::in($this->statusValues())],
        ], [
            'date' => ':attributeには正しい日付を指定してください。',
            'in' => '選択された:attributeは無効な値です。',
        ], [
            'name' => '氏名',
            'date_from' => '受付日時（从）',
            'date_to' => '受付日時（至）',
            'status.*' => 'ステータス',
        ]);

        $contacts = Contact::query()
            ->when($filters['name'] ?? null, fn ($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['status'] ?? null, fn ($query, $statuses) => $query->whereIn('status', $statuses))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'statuses' => ContactStatus::cases(),
            'filters' => $filters,
        ]);
    }

    /**
     * お問い合わせ詳細を表示
     */
    public function show(Contact $contact): View
    {
        return view('admin.contacts.show', [
            'contact' => $contact,
            'statuses' => ContactStatus::cases(),
        ]);
    }

    /**
     * ステータスを更新
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->statusValues())],
        ], [
            'required' => ':attributeを選択してください。',
            'in' => '選択された:attributeは無効な値です。',
        ], [
            'status' => 'ステータス',
        ]);

        $contact->update($validated);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status_updated', true);
    }

    /**
     * ステータスenumの値一覧（バリデーション用）
     *
     * @return array<int, string>
     */
    private function statusValues(): array
    {
        return array_map(fn (ContactStatus $status) => $status->value, ContactStatus::cases());
    }
}
