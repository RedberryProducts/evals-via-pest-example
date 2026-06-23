<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5.4-nano-2026-03-17')]
class ContactExtractorAgent implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are ContactExtractorAgent. Extract support triage data into structured output only.

Return a JSON object with these fields:
- customer.name
- customer.email
- ticket.topic
- ticket.priority
- risk.level

Classify topic as one of: billing, technical, account, feature_request, other.
Classify priority as one of: low, medium, high, urgent.
Classify risk.level as one of: safe, review_required, urgent_review.
Do not add any extra prose.
INSTRUCTIONS;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'customer' => $schema->object([
                'name' => $schema->string()->required(),
                'email' => $schema->string()->required(),
            ])->required(),
            'ticket' => $schema->object([
                'topic' => $schema->string()->enum(['billing', 'technical', 'account', 'feature_request', 'other'])->required(),
                'priority' => $schema->string()->enum(['low', 'medium', 'high', 'urgent'])->required(),
            ])->required(),
            'risk' => $schema->object([
                'level' => $schema->string()->enum(['safe', 'review_required', 'urgent_review'])->required(),
            ])->required(),
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
