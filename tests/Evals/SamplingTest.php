<?php

use App\Ai\Agents\VariableReplyAgent;
use Illuminate\Support\Collection;
use Redberry\Evals\Contracts\Judge;
use Redberry\Evals\EvalContext;
use Redberry\Evals\EvalResult;
use Redberry\Evals\JudgeResult;
use Redberry\Evals\SampleResults;

it('supports repeated sampled deterministic assertions', function () {
    if (! env('RUN_LIVE_EVALS')) {
        VariableReplyAgent::fake([
            'Our refund policy allows a full refund within 14 days.',
            'Customers can request a refund within 14 days of purchase.',
            'Support hours are 9 AM to 5 PM EST, Monday through Friday.',
            'Our support team is available 9 AM to 5 PM EST on weekdays.',
        ])->preventStrayPrompts();
    }

    evaluate(VariableReplyAgent::class)
        ->prompt('Tell me the refund policy in one short sentence.')
        ->samples(2)
        ->assertContains('14 days')
        ->assertLengthLessThan(100);

    evaluate(VariableReplyAgent::class)
        ->prompt('Tell me the support hours in one short sentence.')
        ->repeat(2)
        ->assertContainsAny(['support', 'hours'])
        ->assertNotEmpty();
})->group('sampling');

it('allows a minimum pass count for sampled custom judges', function () {
    if (! env('RUN_LIVE_EVALS')) {
        VariableReplyAgent::fake([
            'Refunds are available within 14 days of purchase.',
            'Please contact support if you need help with your order.',
            'We can help if you share more details about the issue.',
        ])->preventStrayPrompts();
    }

    $judge = new class implements Judge {
        public function evaluate(EvalContext $context): JudgeResult
        {
            $text = strtolower($context->output);
            $passed = str_contains($text, 'refund') || str_contains($text, 'support');

            return new JudgeResult(
                passed: $passed,
                score: $passed ? 100 : 0,
                reasoning: $passed
                    ? 'Reply references refund or support guidance.'
                    : 'Reply does not mention refund or support guidance.',
            );
        }
    };

    evaluate(VariableReplyAgent::class)
        ->prompt('How should I answer a customer asking for a refund?')
        ->samples(3, minimum: 2)
        ->assertPasses($judge);
})->group('sampling');

it('exposes sample results from run and judge', function () {
    if (! env('RUN_LIVE_EVALS')) {
        VariableReplyAgent::fake([
            'Refunds are available within 14 days of purchase.',
            'Our refund window is 14 days.',
        ])->preventStrayPrompts();
    }

    $samples = evaluate(VariableReplyAgent::class)
        ->prompt('Summarize the refund policy briefly.')
        ->samples(2)
        ->run();

    expect($samples)->toBeInstanceOf(SampleResults::class);
    expect($samples->count())->toBe(2);
    expect($samples->outputs())->toHaveCount(2);
    expect($samples->first())->toBeInstanceOf(EvalResult::class);
    expect($samples->last())->toBeInstanceOf(EvalResult::class);
    expect($samples->first()->text)->toContain('14 days');

    $judged = $samples->withJudgeResults(new Collection([
        new JudgeResult(passed: true, score: 100, reasoning: 'Reply mentions the refund window.'),
        new JudgeResult(passed: true, score: 100, reasoning: 'Reply mentions the refund window.'),
    ]));

    expect($judged)->toBeInstanceOf(SampleResults::class);
    expect($judged->judgeResults())->not->toBeNull();
    expect($judged->judgeResults())->toHaveCount(2);
    expect($judged->passRate())->toBe(100.0);
    expect($judged->passed())->toBeTrue();

    $judged->each(function (EvalResult $result): void {
        expect($result->text)->toContain('14 days');
    });
})->group('sampling');
