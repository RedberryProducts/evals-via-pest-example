<?php

declare(strict_types=1);

namespace App\Evals\Judges;

use Redberry\Evals\Contracts\Judge;
use Redberry\Evals\EvalContext;
use Redberry\Evals\JudgeResult;

final class StructuredFieldJudge implements Judge
{
    /**
     * @param  array<string, mixed>  $expectedFields
     */
    public function __construct(private array $expectedFields) {}

    public function evaluate(EvalContext $context): JudgeResult
    {
        $structured = $context->result->structured;

        if (! is_array($structured)) {
            return new JudgeResult(
                passed: false,
                score: 0,
                reasoning: 'Output does not contain structured data.',
            );
        }

        $mismatches = [];

        foreach ($this->expectedFields as $path => $expectedValue) {
            $actualValue = data_get($structured, $path);

            if ($actualValue !== $expectedValue) {
                $mismatches[] = sprintf('%s expected %s but found %s', $path, json_encode($expectedValue), json_encode($actualValue));
            }
        }

        if ($mismatches === []) {
            return new JudgeResult(
                passed: true,
                score: 100,
                reasoning: 'Structured output matches all expected fields.',
            );
        }

        return new JudgeResult(
            passed: false,
            score: 0,
            reasoning: 'Structured field mismatch: '.implode('; ', $mismatches),
        );
    }
}
