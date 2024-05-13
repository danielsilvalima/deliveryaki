<?php

namespace App\Http\Controllers\user_interface;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Dropdowns extends Controller
{
  public function index()
  {
    return view('content.user-interface.ui-dropdowns')->with(['email' => Auth::user()->email]);
  }
}
