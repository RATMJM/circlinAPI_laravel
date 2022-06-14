<?php

use App\Http\Controllers\SNS;
use Illuminate\Support\Facades\Route;


Route::get('/privacy', [SNS\PrivacyController::class, 'index']);
