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
     * お問い合わせ一覧を表示
     */
    public function index(): View
    {
        $contacts = Contact::query()
            ->latest()
            ->paginate(20);

        return view('admin.contacts.index', [
            'contacts' => $contacts,
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
            'status' => ['required', Rule::enum(ContactStatus::class)],
        ]);

        $contact->update($validated);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('status_updated', true);
    }
}
