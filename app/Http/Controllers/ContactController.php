<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_website' => ['nullable', 'max:255'], // honeypot
            'name'            => ['required', 'max:180'],
            'email'           => ['required', 'email', 'max:190'],
            'phone'           => ['nullable', 'max:60'],
            'service'         => ['required', 'max:120'],
            'message'         => ['required', 'max:5000'],
            'contact_pref'    => ['nullable', 'in:Email,Phone,WhatsApp'],
            'attachment'      => ['nullable', 'file', 'max:8192', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'consent'         => ['required', 'boolean'],
        ]);

        // Honeypot: if filled, silently accept but do nothing
        if (!empty($data['company_website'])) {
            return back()->with('contact_ok', 'Thanks! We’ll be in touch soon.');
        }

        // Normalize inputs for de-dupe comparison
        $email   = strtolower(trim($data['email']));
        $phone   = trim((string) ($data['phone'] ?? ''));
        $service = trim($data['service']);
        $message = Str::squish((string) $data['message']); // collapse whitespace

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('contact_attachments', 'public');
        }

        // ===== De-dupe guard (same email/phone/service/message in last 2 minutes) =====
        $isDuplicate = DB::table('contact_messages')
            ->where('email', $email)
            ->where('service', $service)
            ->where('message', $message)
            ->when($phone !== '', fn ($q) => $q->where('phone', $phone),
                                fn ($q) => $q->whereNull('phone'))
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();

        if (!$isDuplicate) {
            DB::table('contact_messages')->insert([
                'name'            => $data['name'],
                'email'           => $email,
                'phone'           => $phone !== '' ? $phone : null,
                'service'         => $service,
                'message'         => $message,
                'contact_pref'    => $data['contact_pref'] ?? null,
                'attachment_path' => $path,
                'consent'         => !empty($data['consent']) ? 1 : 0,
                'ip'              => $request->ip(),
                'ua'              => substr((string) $request->userAgent(), 0, 255),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        \App\Support\ToolActivity::bump('leads', 0);
        return back()->with('contact_ok', 'Thanks! We’ll be in touch soon.');
    }
}
