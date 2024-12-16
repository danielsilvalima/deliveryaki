<?php

namespace App\Http\Controllers\form_layouts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorizontalForm extends Controller
{
  public function index()
  {
    return view('content.form-layout.form-layouts-horizontal', ['email' => Auth::user()->email]);
  }
}
