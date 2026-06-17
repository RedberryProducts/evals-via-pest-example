<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5.4-nano-2026-03-17')]
class SupportPolicyAgent implements Agent, HasProviderOptions
{
    use Promptable;

    public function __construct(public ?string $category = null) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $categoryGuidance = $this->category ? "Filter your response for category: {$this->category}." : '';

        return <<<INSTRUCTIONS
You are SupportPolicyAgent. Answer customer inquiries about our support policy concisely and accurately.
Our refund policy states:
- Customers can request a full refund within 14 days of purchase.
- Refunds typically take 5-10 business days to appear in your bank account.
Our support hours are 9 AM to 5 PM EST, Monday through Friday.

For JSON responses, provide a JSON object containing the answer, category, and status.
{$categoryGuidance}
INSTRUCTIONS;
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
