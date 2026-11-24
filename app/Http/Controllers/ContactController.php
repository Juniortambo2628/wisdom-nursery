<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactForm;
use App\Models\ContactMessage;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactForm $request): RedirectResponse
    {
        $data = $request->validated();
        ContactMessage::create([
            'name' => $data['name'],
            'phone' => Phone::normalise($data['phone']),
            'message' => $data['message'],
        ]);

        return back()->with('contact_sent', true)->with('status', 'Thank you. We will get back to you soon.');
    }
}
