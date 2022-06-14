<?php

namespace App\Http\Controllers\SNS;
use App\Http\Controllers\Controller;

class PrivacyController extends Controller
{
    public function index()
    {
        return view('SNS.privacy.index', []);
    }
}
