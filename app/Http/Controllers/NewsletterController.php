<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email','max:190'],
        ]);

        // UPSERT: ignore duplicates
        DB::statement(
            'INSERT INTO newsletter_subscriptions (email, created_at, updated_at)
             VALUES (?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)',
            [$data['email']]
        );

        \App\Support\ToolActivity::bump('leads', 0); // 0 = global bump
        return back()->with('newsletter_ok', 'Thanks for subscribing!');
    }
}
