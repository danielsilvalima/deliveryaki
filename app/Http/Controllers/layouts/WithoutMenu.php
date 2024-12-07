<?php

namespace App\Http\Controllers\layouts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithoutMenu extends Controller
{
  public function index()
  {

    return view('content.layouts-example.layouts-without-menu', ['email' => Auth::user()->email]);
  }
}
