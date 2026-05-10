<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::OpenAI)]
#[Model('gpt-5-nano')]
class SupportReplyAgent implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are SupportReplyAgent. Generate a human-facing support reply from only the original customer message.

Keep the response polite, empathetic, and concise.
Ask for the next useful detail when the message lacks enough information to act.
Avoid blame, defensiveness, or language that makes the customer feel at fault.
Do not make unsafe promises, including instant refund guarantees, guaranteed fixes, account changes, or exact timelines.
Do not reference triage output, internal labels, tool data, or hidden workflow details.
INSTRUCTIONS;
    }
}
