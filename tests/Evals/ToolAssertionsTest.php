<?php

use App\Ai\Agents\ToolWorkflowAgent;
use App\Ai\Tools\BillingHistoryTool;
use App\Ai\Tools\CustomerLookupTool;
use App\Ai\Tools\EscalationPolicyTool;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Responses\Data\ToolResult;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\TextResponse;
use Redberry\Evals\EvalResult;
use Redberry\Evals\ToolInvocation;

it('asserts tool usage, sequence, and counts for billing workflows', function () {
    if (! env('RUN_LIVE_EVALS')) {
        $response = (new TextResponse(
            'Customer is escalated to the billing team.',
            new Usage(promptTokens: 18, completionTokens: 22),
            new Meta('openai', 'gpt-5-nano'),
        ))->withToolCallsAndResults(
            collect([
                new ToolCall('call_1', 'CustomerLookupTool', [
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'auto',
                ]),
                new ToolCall('call_2', 'BillingHistoryTool', [
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'email',
                ]),
                new ToolCall('call_3', 'EscalationPolicyTool', [
                    'ticket_category' => 'billing',
                    'priority' => 'high',
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
                new ToolResult('call_2', 'BillingHistoryTool', [
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'email',
                ], json_encode([
                    'matched' => true,
                    'customer_identifier' => 'john@example.com',
                    'lookup_by' => 'email',
                    'customer_id' => 'cus_1001',
                    'name' => 'John Carter',
                    'email' => 'john@example.com',
                    'billing_status' => 'past_due',
                    'open_invoices' => 2,
                    'last_payment_status' => 'past_due',
                    'recommended_action' => 'request_invoice_details',
                    'source_reason' => 'explicit_identifier',
                ], JSON_THROW_ON_ERROR)),
                new ToolResult('call_3', 'EscalationPolicyTool', [
                    'ticket_category' => 'billing',
                    'priority' => 'high',
                ], json_encode([
                    'ticket_category' => 'billing',
                    'priority' => 'high',
                    'route_to' => 'billing_escalations',
                    'requires_supervisor_review' => true,
                    'sla_hours' => 4,
                    'reason' => 'escalate_to_supervisor',
                ], JSON_THROW_ON_ERROR)),
            ]),
        );

        ToolWorkflowAgent::fake([$response])->preventStrayPrompts();
    }

    $result = evaluate(ToolWorkflowAgent::class)
        ->prompt('Customer john@example.com says they were charged twice and need a refund.')
        ->assertToolUsed(CustomerLookupTool::class, [
            'customer_identifier' => 'john@example.com',
            'lookup_by' => 'auto',
        ])
        ->assertToolUsed(BillingHistoryTool::class, function (ToolInvocation $invocation): bool {
            $result = json_decode((string) $invocation->result, true, flags: JSON_THROW_ON_ERROR);

            return $invocation->customer_identifier === 'john@example.com'
                && $invocation->lookup_by === 'email'
                && $result['billing_status'] === 'past_due'
                && $result['open_invoices'] === 2;
        })
        ->assertToolUsed(EscalationPolicyTool::class, function (ToolInvocation $invocation): bool {
            $result = json_decode((string) $invocation->result, true, flags: JSON_THROW_ON_ERROR);

            return $invocation->ticket_category === 'billing'
                && $invocation->priority === 'high'
                && $result['route_to'] === 'billing_escalations'
                && $result['requires_supervisor_review'] === true;
        })
        ->assertToolUseSequence([
            CustomerLookupTool::class,
            BillingHistoryTool::class,
            EscalationPolicyTool::class,
        ])
        ->assertToolUsedTimes(CustomerLookupTool::class, 1)
        ->assertToolUsedAtLeast(BillingHistoryTool::class, 1)
        ->assertToolUsedAtMost(EscalationPolicyTool::class, 1)
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->toolInvocations)->toHaveCount(3);
    expect($result->text)->toBe('Customer is escalated to the billing team.');
})->group('tools');

it('asserts that feature requests do not use billing tools', function () {
    if (! env('RUN_LIVE_EVALS')) {
        ToolWorkflowAgent::fake([
            new TextResponse(
                'This is a feature request, so no tools are needed.',
                new Usage(promptTokens: 12, completionTokens: 14),
                new Meta('openai', 'gpt-5-nano'),
            ),
        ])->preventStrayPrompts();
    }

    $result = evaluate(ToolWorkflowAgent::class)
        ->prompt('Can you add dark mode to the dashboard?')
        ->assertToolNotUsed(CustomerLookupTool::class)
        ->assertToolNotUsed(BillingHistoryTool::class)
        ->assertToolNotUsed(EscalationPolicyTool::class)
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->toolInvocations)->toBeEmpty();
})->group('tools');
