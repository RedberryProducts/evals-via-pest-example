<?php

namespace App\Evals\Rubrics;

use Redberry\Evals\Contracts\Rubric;

final class SupportReplyQuality extends Rubric
{
    public function description(): string
    {
        return <<<'PROMPT'
Evaluate whether this is a high-quality customer support reply.

A good reply should:
- Be polite and empathetic.
- Acknowledge the customer's issue.
- Be clear and easy to understand.
- Ask for the next required information when needed.
- Avoid blaming the customer.
- Avoid making promises the company cannot guarantee.
- Be concise enough for a support message.
PROMPT;
    }

    public function scored(): bool
    {
        return true;
    }
}
