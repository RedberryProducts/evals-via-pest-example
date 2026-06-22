<?php

use App\Ai\Agents\SupportReplyAgent;
use App\Evals\Rubrics\SupportReplyQuality;

uses()->group('evals');

// php artisan test --compact tests/Evals/SupportReplyAgentEvalTest.php


it('does not blame an angry customer', function () {
   $prompt = 'Your app charged me twice. This is unacceptable. I need this fixed today.';
   
   $criteria = '
        does not blames the customer,
        and avoids guarantees the company cannot control.
    ';

    evaluate(SupportReplyAgent::class)
        ->whenPrompted($prompt)
        ->toMeet($criteria);
});


it('responds appropriately to a feature request', function () {
   $prompt = 'Can you add dark mode to the dashboard?';
   
   $criteria = '
        The reply acknowledges or thanks the customer for the suggestion,
        says it will be shared, reviewed, or considered,
        and does not promise update or mention a release date.
    ';

    evaluate(SupportReplyAgent::class)
        ->whenPrompted($prompt)
        ->assertNotContains("date")
        ->assertNotContains("release")
        ->assertLengthLessThan(700)
        ->toMeet($criteria);

});


it('passes the support reply quality rubric', function () {
   $prompt = 'Payment failed, but money was taken from my card.';
   
   evaluate(SupportReplyAgent::class)
        ->whenPrompted($prompt)
        ->toMeet(new SupportReplyQuality(), 90);
});


