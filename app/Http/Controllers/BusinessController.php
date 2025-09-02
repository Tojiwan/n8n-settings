<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Business;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::with('bot')->get();
        return view('businesses.index', compact('businesses'));
    }
}