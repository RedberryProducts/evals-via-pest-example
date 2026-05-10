<?php

use App\Ai\Agents\SupportReplyAgent;
use App\Ai\Agents\TicketTriageAgent;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/case-1', function () {
    $customerMessage = 'Hi, I was charged twice this month and I am really frustrated. Can someone check my account?';

    $triage = app(TicketTriageAgent::class, [
        'customerIdentifier' => 'john@example.com',
    ])->prompt($customerMessage);

    $reply = app(SupportReplyAgent::class)->prompt($customerMessage);

    return response()->json([
        'message' => $customerMessage,
        'triage' => $triage->toArray(),
        'reply' => $reply->text,
    ]);
});
