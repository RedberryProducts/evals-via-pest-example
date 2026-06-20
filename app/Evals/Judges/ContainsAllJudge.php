<?php

declare(strict_types=1);

namespace App\Evals\Judges;

use Redberry\Evals\Contracts\Judge;
use Redberry\Evals\EvalContext;
use Redberry\Evals\JudgeResult;

final class ContainsAllJudge implements Judge
{
    /**
     * @param  array<int, string>  $requiredTerms
     */
    public function __construct(private array $requiredTerms) {}

    public function evaluate(EvalContext $context): JudgeResult
    {
        $output = mb_strtolower($context->output);
        $missingTerms = array_values(array_filter(
            $this->requiredTerms,
            fn (string $term): bool => ! str_contains($output, mb_strtolower($term)),
        ));

        if ($missingTerms === []) {
            return new JudgeResult(
                passed: true,
                score: 100,
                reasoning: 'Output contains all required terms.',
            );
        }

        return new JudgeResult(
            passed: false,
            score: 0,
            reasoning: 'Missing required terms: '.implode(', ', $missingTerms),
        );
    }
}
