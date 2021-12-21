<?php

use Illuminate\Support\Facades\Route;

Route::get('/sendRequest', '\ItCpp\Sendrequest\SendRequest@send');