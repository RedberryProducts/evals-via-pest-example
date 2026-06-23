<?php

namespace App\Ai\Agents;

use App\Ai\Tools\BillingHistoryTool;
use App\Ai\Tools\CustomerLookupTool;
use App\Ai\Tools\EscalationPolicyTool;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5.4-nano-2026-03-17')]
class ToolWorkflowAgent implements Agent, HasProviderOptions, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are ToolWorkflowAgent. Decide whether the prompt is billing/account-sensitive.

If it is billing/account-sensitive, call the tools in this order:
1. CustomerLookupTool with the customer's identifier and lookup_by set to email when the identifier looks like an email, otherwise name.
2. BillingHistoryTool with the same customer_identifier and the same lookup_by as step 1.
3. EscalationPolicyTool with ticket_category billing for payment/refund issues or account for access issues, and priority high unless the prompt clearly requires urgent review.

If the prompt is a feature request, do not call any tools.
Return a short support summary only.
INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new CustomerLookupTool,
            new BillingHistoryTool,
            new EscalationPolicyTool,
        ];
    }

    /**
     * Get provider-specific generation options.
     */
    public function providerOptions(Lab|string $provider): array
    {
        return match ($provider) {
            Lab::OpenAI => [
                'reasoning' => ['effort' => 'low'],
            ],
            default => [],
        };
    }
}
