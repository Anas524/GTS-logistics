<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $tab = $request->get('tab', 'contacts'); // contacts | newsletter

        if ($tab === 'newsletter') {
            $newsletter = DB::table('newsletter_subscriptions')
                ->when($q, fn($sql) => $sql->where('email','like',"%$q%"))
                ->orderByDesc('created_at')
                ->paginate(20)->withQueryString();

            return view('admin.leads', [
                'tab' => 'newsletter',
                'newsletter' => $newsletter,
                'contacts' => null,
                'q' => $q,
            ]);
        }

        // default: contacts
        $contacts = DB::table('contact_messages')
            ->when($q, function ($sql) use ($q) {
                return $sql->where(function ($w) use ($q) {
                    $w->where('name','like',"%$q%")
                      ->orWhere('email','like',"%$q%")
                      ->orWhere('phone','like',"%$q%")
                      ->orWhere('service','like',"%$q%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('admin.leads', [
            'tab' => 'contacts',
            'contacts' => $contacts,
            'newsletter' => null,
            'q' => $q,
        ]);
    }
}
