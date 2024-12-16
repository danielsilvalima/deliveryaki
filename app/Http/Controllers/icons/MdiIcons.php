<?php

namespace App\Http\Controllers\icons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MdiIcons extends Controller
{
  public function index()
  {
    return view('content.icons.icons-mdi')->with(['email' => Auth::user()->email]);
  }
}
