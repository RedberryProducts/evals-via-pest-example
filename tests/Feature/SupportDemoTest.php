<?php

use App\Ai\Agents\SupportReplyAgent;
use App\Ai\Agents\TicketTriageAgent;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Responses\Data\ToolResult;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredTextResponse;

it('renders the support demo tickets', function () {
    $response = $this->get(route('support-demo.index'));

    $response->assertSuccessful()
        ->assertSee('Support demo console')
        ->assertSee('Payment failed, but money was taken from my card.')
        ->assertSee('Can you add dark mode to the dashboard?')
        ->assertSee('Triage')
        ->assertDontSee('feature_request')
        ->assertDontSee('urgent');
});

it('renders triage output reply and customer lookup tool usage', function () {
    TicketTriageAgent::fake([
        (new StructuredTextResponse(
            [
                'category' => 'billing',
                'priority' => 'high',
                'sentiment' => 'frustrated',
                'summary' => 'Customer reports a failed payment while their card was charged.',
                'needs_human_review' => true,
            ],
            'Structured triage response',
            new Usage(promptTokens: 10, completionTokens: 20),
            new Meta('openai', 'gpt-5-nano'),
        ))->withToolCallsAndResults(
            collect([
                new ToolCall('call_1', 'CustomerLookupTool', [
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'auto',
                ]),
            ]),
            collect([
                new ToolResult('call_1', 'CustomerLookupTool', [
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'auto',
                ], json_encode([
                    'matched' => true,
                    'customer_id' => 'cus_1001',
                    'name' => 'John Carter',
                    'email' => 'john@example.com',
                    'plan' => 'Pro',
                    'payment_status' => 'past_due',
                    'previous_tickets_count' => 4,
                    'account_state' => 'active',
                    'source_reason' => 'explicit_identifier',
                ], JSON_THROW_ON_ERROR)),
            ]),
        ),
    ])->preventStrayPrompts();

    SupportReplyAgent::fake([
        'Hi, sorry about this. Please send the transaction ID and our team will review it. Here is what helps: - Transaction ID - Payment email',
    ])->preventStrayPrompts();

    $response = $this->post(route('support-demo.triage'), [
        'ticket_id' => 'billing-failed-charge',
    ]);

    $response->assertSuccessful()
        ->assertSee('Structured triage')
        ->assertSee('Customer reports a failed payment while their card was charged.')
        ->assertSee('Suggested reply')
        ->assertSee('Please send the transaction ID')
        ->assertSee("helps:\n- Transaction ID\n- Payment email")
        ->assertSee('CustomerLookupTool used: Yes')
        ->assertSee('john@example.com')
        ->assertSee('past_due');
});

it('requires a known demo ticket', function () {
    $response = $this->post(route('support-demo.triage'), [
        'ticket_id' => 'missing-ticket',
    ]);

    $response->assertSessionHasErrors('ticket_id');
});

it('renders an error state when the ai provider fails', function () {
    TicketTriageAgent::fake([
        fn () => throw new RuntimeException('Provider timed out.'),
    ])->preventStrayPrompts();

    SupportReplyAgent::fake()->preventStrayPrompts();

    $response = $this->post(route('support-demo.triage'), [
        'ticket_id' => 'billing-failed-charge',
    ]);

    $response->assertSuccessful()
        ->assertSee('Workflow did not complete')
        ->assertSee('The AI provider did not return a response before the demo timeout.')
        ->assertSee('Payment failed, but money was taken from my card.');
});
