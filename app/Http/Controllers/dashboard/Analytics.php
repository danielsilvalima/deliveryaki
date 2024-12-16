<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Analytics extends Controller
{
  public function index()
  {
    return view('content.dashboard.dashboards-analytics')->with(['email' => Auth::user()->email]);
  }
}
