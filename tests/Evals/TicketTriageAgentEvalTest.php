<?php

use App\Ai\Agents\TicketTriageAgent;
use App\Ai\Tools\CustomerLookupTool;
use Redberry\Evals\ToolInvocation;

uses()->group('evals');

// php artisan test --compact tests/Evals/TicketTriageAgentEvalTest.php

it('returns required triage fields', function () {
   $prompt = 'Payment failed, but money was taken from my card.';

   evaluate(TicketTriageAgent::class)
       ->whenPrompted($prompt)
       ->assertArray()
       ->assertHasKeys([
           'category',
           'priority',
           'sentiment',
           'summary',
           'needs_human_review',
       ]);
});


it('uses customer lookup tool on failed payments', function () {
   $prompt = 'Payment failed, but money was taken from my card.';

   evaluate(fn () => TicketTriageAgent::make(customerIdentifier: 'john@example.com'))
       ->whenPrompted($prompt)
       ->assertToolUsed(CustomerLookupTool::class, 
       function (ToolInvocation $tool): bool {
           return $tool->customer_identifier === 'john@example.com';
       });
});


it('classifies angry double charges as urgent billing tickets', function () {
   $prompt = 'Your app charged me twice. This is unacceptable. I need this fixed today.';
   
   evaluate(fn () => TicketTriageAgent::make(customerIdentifier: 'john@example.com'))
      ->whenPrompted($prompt)
      ->toBe([
           'category' => 'billing',
           'priority' => 'urgent',
           'sentiment' => 'angry',
           'needs_human_review' => true,
       ]);
});

