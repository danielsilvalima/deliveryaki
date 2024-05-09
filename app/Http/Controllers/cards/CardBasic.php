<?php

namespace App\Http\Controllers\cards;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardBasic extends Controller
{
  public function index()
  {
    return view('content.cards.cards-basic')->with(['email' => Auth::user()->email]);
  }
}
