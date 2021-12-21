<?php

use Illuminate\Support\Facades\Route;

Route::post('/itcpp/sendrequest', '\ItCpp\Sendrequest\SendRequest@send')->middleware('web');