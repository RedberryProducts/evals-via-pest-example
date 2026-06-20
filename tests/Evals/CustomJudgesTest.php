<?php

use App\Ai\Agents\ContactExtractorAgent;
use App\Ai\Agents\SupportReplyAgent;
use App\Evals\Judges\ContainsAllJudge;
use App\Evals\Judges\StructuredFieldJudge;
use Redberry\Evals\EvalContext;

it('uses a local text judge and returns reasoning', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportReplyAgent::fake(fn () => 'I am sorry this happened. Please send the transaction ID and payment email so we can review it.')->preventStrayPrompts();
    }

    $result = evaluate(SupportReplyAgent::class)
        ->prompt('My card was charged twice and I need help.')
        ->run();

    $judge = new ContainsAllJudge(['transaction ID', 'payment email']);
    $judgeResult = $judge->evaluate(new EvalContext(
        input: 'My card was charged twice and I need help.',
        output: $result->text,
        expected: null,
        result: $result,
    ));

    expect($judgeResult->passed)->toBeTrue();
    expect($judgeResult->score)->toBe(100);
    expect($judgeResult->reasoning)->toContain('all required terms');

    evaluate(SupportReplyAgent::class)
        ->prompt('My card was charged twice and I need help.')
        ->assertPasses($judge);
})->group('judges');

it('uses a local structured judge against nested output', function () {
    $expected = [
        'customer' => [
            'name' => 'John Carter',
            'email' => 'john@example.com',
        ],
        'ticket' => [
            'topic' => 'billing',
            'priority' => 'high',
        ],
        'risk' => [
            'level' => 'review_required',
        ],
    ];

    if (! env('RUN_LIVE_EVALS')) {
        ContactExtractorAgent::fake(fn () => $expected)->preventStrayPrompts();
    }

    $result = evaluate(ContactExtractorAgent::class)
        ->prompt('Extract the support triage details for John Carter, john@example.com, billing issue, review required.')
        ->run();

    $judge = new StructuredFieldJudge([
        'customer.name' => 'John Carter',
        'customer.email' => 'john@example.com',
        'ticket.topic' => 'billing',
        'risk.level' => 'review_required',
    ]);

    $judgeResult = $judge->evaluate(new EvalContext(
        input: 'Extract the support triage details for John Carter, john@example.com, billing issue, review required.',
        output: $result->text,
        expected: $expected,
        result: $result,
    ));

    expect($judgeResult->passed)->toBeTrue();
    expect($judgeResult->score)->toBe(100);
    expect($judgeResult->reasoning)->toContain('matches all expected fields');

    evaluate(ContactExtractorAgent::class)
        ->prompt('Extract the support triage details for John Carter, john@example.com, billing issue, review required.')
        ->assertPasses($judge);
})->group('judges');
