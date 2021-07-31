<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index(){
        return view('login');
        // dd(auth()->user());
    }

    public function store(Request $request){
        auth()->attempt($request->only('email', 'password'), $request->remember);

        dd(auth()->user());
    }
}
