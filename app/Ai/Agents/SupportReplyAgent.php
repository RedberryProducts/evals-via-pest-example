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
class SupportReplyAgent implements Agent, HasProviderOptions
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are SupportReplyAgent. Generate a human-facing support reply from the original customer message.

Write 2 to 4 short sentences only.
Keep the response polite, empathetic, and concise.
Ask for the next useful detail in one sentence when the message lacks enough information to act.
Avoid blame, defensiveness, or language that makes the customer feel at fault.
Do not make unsafe promises, including instant refund guarantees, guaranteed fixes, account changes, or exact timelines.
Do not provide a long troubleshooting checklist.
Do not reference triage output, internal labels, tool data, or hidden workflow details.
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
