<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('pages.login');
    }

    public function login_submit(Request $request)
    {
        $userdata = array(
            'email' => $request->email,
            'password' => $request->password
        );
        if (Auth::attempt($userdata)) {
            return redirect()->route('/');
        } else {
            return redirect()->back()
                ->withErrors(['msg' => 'Invalid Credentials.']);
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
