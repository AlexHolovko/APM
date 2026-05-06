<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
  public function index()
  {
    $user = Auth::user(); 
    return view('profile.index', compact('user'));
  }

  public function updatePassword(Request $request)
  {
    $request->validate([
      'password' => 'required|min:6|confirmed',
    ]);

    $user = Auth::user();
    $user->password = Hash::make($request->password);
    $user->save();

    return back()->with('success', 'Пароль обновлён');
  }
}
