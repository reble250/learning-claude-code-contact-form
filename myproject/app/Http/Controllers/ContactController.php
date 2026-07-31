<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * お問い合わせ入力フォームを表示
     */
    public function create(): View
    {
        return view('contact.create');
    }

    /**
     * 入力内容をバリデーションし、確認画面を表示
     */
    public function confirm(ContactRequest $request): View
    {
        return view('contact.confirm', [
            'input' => $request->validated(),
        ]);
    }

    /**
     * 確認画面から入力画面へ戻る（入力内容を保持したままリダイレクト）
     */
    public function back(Request $request): RedirectResponse
    {
        return redirect()
            ->route('contact.create')
            ->withInput($request->only(['name', 'email', 'subject', 'body']));
    }

    /**
     * お問い合わせ内容を保存し、完了画面へリダイレクト
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        Contact::create([
            ...$request->validated(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        session(['contact.submitted' => true]);

        return redirect()->route('contact.thanks');
    }

    /**
     * 送信完了画面を表示
     */
    public function thanks(): View|RedirectResponse
    {
        if (! session()->pull('contact.submitted')) {
            return redirect()->route('contact.create');
        }

        return view('contact.thanks');
    }
}
