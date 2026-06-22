# PEST Plugin Evals usage examples

```php

it('returns required triage fields', function () {
   evaluate(TicketTriageAgent::class)
       ->whenPrompted('Payment failed, but money was taken from my card.')
       ->assertArray()
       ->assertHasKeys([
           'category',
           'priority',
           'sentiment',
           'summary',
           'needs_human_review',
       ]);
});

it('classifies failed charged payments as billing high priority', function () {
   evaluate(fn () => TicketTriageAgent::make(customerIdentifier: 'john@example.com'))
       ->whenPrompted('Payment failed, but money was taken from my card.')
       ->assertToolUsed(CustomerLookupTool::class, function (ToolInvocation $tool): bool {
           return $tool->customer_identifier === 'john@example.com';
       });
});

it('classifies angry double charges as urgent billing tickets', function () {
   evaluate(fn () => TicketTriageAgent::make(customerIdentifier: 'nora@example.com'))
       ->whenPrompted('Your app charged me twice. This is unacceptable. I need this fixed today.')
       ->assertToolUsed(CustomerLookupTool::class)
       ->toBe([
           'category' => 'billing',
           'priority' => 'urgent',
           'sentiment' => 'angry',
           'needs_human_review' => true,
       ]);
});


```

---


```php

it('does not blame an angry customer', function () {
   evaluate(SupportReplyAgent::class)
       ->whenPrompted('Your app charged me twice. This is unacceptable. I need this fixed today.')
       ->toMeet('
           does not blames the customer,
           and avoids guarantees the company cannot control.
       ');
});


it('responds appropriately to a feature request', function () {
   evaluate(SupportReplyAgent::class)
       ->whenPrompted('Can you add dark mode to the dashboard?')
       ->assertString()
       ->assertNotEmpty()
       ->assertLengthLessThan(700)
       ->assertNotContains('date')
       ->assertNotContains('release')
       ->toMeet('
           The reply acknowledges or thanks the customer for the suggestion,
           says it will be shared, reviewed, or considered,
           and does not promise update or mention a release date.
       ', 80);
});


it('passes the support reply quality rubric', function () {
   $result = evaluate(SupportReplyAgent::class)
       ->whenPrompted('Payment failed, but money was taken from my card.')
       ->toMeet(new SupportReplyQuality, 90);
});


```