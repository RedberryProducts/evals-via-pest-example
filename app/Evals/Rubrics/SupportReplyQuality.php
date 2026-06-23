<?php

declare(strict_types=1);

namespace App\Evals\Rubrics;

use Redberry\Evals\Contracts\Rubric;

final class SupportReplyQuality extends Rubric
{
    public function description(): string
    {
        return 'The reply should be polite, empathetic, clear, avoid blame, avoid unsafe promises, and ask for the next useful detail when more information is needed.';
    }
}
