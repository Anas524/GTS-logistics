<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function updatePassword(Request $req)
    {
        $req->validate([
            'current_password' => ['required'],
            'password'          => ['required','string','min:8','confirmed'],
        ]);

        $user = $req->user();

        if (! Hash::check($req->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->password = Hash::make($req->password);
        $user->save();

        return response()->json(['ok' => true]);
    }
}
