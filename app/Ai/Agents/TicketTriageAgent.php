<?php

namespace App\Ai\Agents;

use App\Ai\Support\CustomerDirectory;
use App\Ai\Tools\CustomerLookupTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5.4-nano-2026-03-17')]
class TicketTriageAgent implements Agent, HasProviderOptions, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(public ?string $customerIdentifier = null) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $customerIdentifier = $this->customerIdentifier ?? (new CustomerDirectory)->random()['email'];

        $identifierGuidance = $this->customerIdentifier === null
            ? "No customer identifier was provided, so use the random demo customer identifier '{$customerIdentifier}'. When a lookup is required, forward this exact value as customer_identifier and set lookup_by to auto."
            : "The constructor-provided customer identifier is '{$customerIdentifier}'. When a lookup is required, forward this exact value as customer_identifier and set lookup_by to auto unless the identifier is clearly an email or name.";

        return <<<INSTRUCTIONS
You are TicketTriageAgent. Convert the original customer message into structured ticket data only.

Classify category as exactly one of: billing, technical, account, feature_request, other.
Classify priority as exactly one of: low, medium, high, urgent.
Classify sentiment as exactly one of: neutral, confused, frustrated, angry.
Write a concise summary of the issue.
Set needs_human_review to true when the message involves billing/account sensitivity, anger, possible account access issues, legal/security risk, refunds, cancellations, payment failure, or anything you cannot confidently classify.

Use CustomerLookupTool when the issue appears billing-sensitive or account-sensitive. Include relevant customer account context from the tool in your reasoning, but return only the required structured fields.
{$identifierGuidance}
If no lookup is needed, do not call a tool.
INSTRUCTIONS;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->enum(['billing', 'technical', 'account', 'feature_request', 'other'])->required(),
            'priority' => $schema->string()->enum(['low', 'medium', 'high', 'urgent'])->required(),
            'sentiment' => $schema->string()->enum(['neutral', 'confused', 'frustrated', 'angry'])->required(),
            'summary' => $schema->string()->required(),
            'needs_human_review' => $schema->boolean()->required(),
        ];
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
