<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScrapedEmail;
use App\Http\Requests\ScrapedEmailRequest;

class ScrapedEmailController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->forget('returnUrl');

        $searchSubject = trim($request->searchSubject);
        $searchSender  = trim($request->searchSender);

        $emails = ScrapedEmail::when($searchSubject, fn($q) =>
                    $q->where('email_subject', 'LIKE', "%{$searchSubject}%")
                )
                ->when($searchSender, fn($q) =>
                    $q->where('from_email', 'LIKE', "%{$searchSender}%")
                )
                ->latest('date_received')
                ->simplePaginate(25);

        return view('scraped_emails.index', compact('emails', 'searchSubject', 'searchSender'));
    }

    public function create(Request $request)
    {
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('scraped_emails.save');
    }

    public function store(ScrapedEmailRequest $request)
    {
        $email = ScrapedEmail::create($request->validated());

        return redirect(
            $request->session()->get('returnUrl', route('scraped-emails.index'))
        )->with('success', "Saved “{$email->email_subject}” successfully.");
    }

    public function show(Request $request, ScrapedEmail $scraped_email)
    {
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('scraped_emails.show', compact('scraped_email'));
    }

    public function edit(Request $request, ScrapedEmail $scraped_email)
    {
        $request->session()->get('returnUrl')
            ?: $request->session()->put('returnUrl', url()->previous());

        return view('scraped_emails.save', compact('scraped_email'));
    }

    public function update(ScrapedEmailRequest $request, ScrapedEmail $scraped_email)
    {
        $scraped_email->update($request->validated());

        return redirect(
            $request->session()->get('returnUrl', route('scraped-emails.index'))
        )->with('success', "Updated “{$scraped_email->email_subject}” successfully.");
    }

    public function destroy(Request $request, ScrapedEmail $scraped_email)
    {
        $scraped_email->delete();

        return back()->with('success', "Deleted “{$scraped_email->email_subject}.”");
    }
}
