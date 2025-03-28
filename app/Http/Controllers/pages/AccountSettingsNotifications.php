<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountSettingsNotifications extends Controller
{
  public function index()
  {
    return view('content.pages.pages-account-settings-notifications', ['email' => Auth::user()->email]);
  }
}
