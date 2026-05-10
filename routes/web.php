<?php

use Illuminate\Support\Facades\Route;
use App\Ai\Agents\SupportAgent;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/case-1', function () {
    $response1 = app(SupportAgent::class)->prompt('I want to delete my account');
    $response2 = app(SupportAgent::class)->prompt("Remove my profile");
    $response3 = app(SupportAgent::class)->prompt("Close my account");

    dd($response1, $response2, $response3);
});