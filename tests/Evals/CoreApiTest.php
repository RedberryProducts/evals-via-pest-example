<?php

use App\Ai\Agents\SupportPolicyAgent;
use Redberry\Evals\EvalCase;
use Redberry\Evals\EvalResult;

test('accepts agent class, instance, and closure', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase.',
            'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase.',
            'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase.',
        ]);
    }

    // Agent class string
    evaluate(SupportPolicyAgent::class)
        ->prompt('What is the refund policy?')
        ->assertNotEmpty();

    // Agent instance
    evaluate(new SupportPolicyAgent)
        ->prompt('What is the refund policy?')
        ->assertNotEmpty();

    // Agent factory closure
    evaluate(fn () => new SupportPolicyAgent)
        ->prompt('What is the refund policy?')
        ->assertNotEmpty();
})->group('deterministic');

test('accepts agent class with constructor args', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'This response is filtered for the billing category.',
        ]);
    }

    $result = evaluate(SupportPolicyAgent::class, constructorArgs: ['category' => 'billing'])
        ->prompt('What is my category?')
        ->run();

    expect($result->text)->toContain('billing');
})->group('deterministic');

test('overrides provider and model inline and fluently', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase.',
            'Our refund policy is extremely generous: customers can request a full refund within 14 days of purchase.',
        ]);
    }

    // Inline overrides
    evaluate(SupportPolicyAgent::class)
        ->prompt(
            'What is the refund policy?',
            provider: 'openai',
            model: 'gpt-4o-mini',
        )
        ->assertNotEmpty();

    // Fluent method overrides
    evaluate(SupportPolicyAgent::class)
        ->provider('openai')
        ->model('gpt-4o-mini')
        ->prompt('What is the refund policy?')
        ->assertNotEmpty();
})->group('deterministic');

test('timeout and EvalCase with prompt only', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
            'Write a short response about our refund policy.',
        ]);
    }

    // Timeout via fluent method
    $result = evaluate(SupportPolicyAgent::class)
        ->timeout(120)
        ->prompt('What are our operating hours?')
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->text)->toContain('9 AM to 5 PM EST');

    // EvalCase with prompt only
    $case = EvalCase::make()
        ->prompt('Tell me about refund window');

    evaluate(SupportPolicyAgent::class)
        ->withCase($case)
        ->assertNotEmpty();
})->group('deterministic');

test('EvalCase with expected and run', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our refund policy allows requests within 14 days.',
        ]);
    }

    $case = EvalCase::make()
        ->prompt('Tell me about the refund period')
        ->expected('Our refund policy allows requests within 14 days.');

    $result = evaluate(SupportPolicyAgent::class)
        ->withCase($case)
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->text)->toContain('14 days');
})->group('deterministic');

test('auto-runs and caches on first assertion', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
        ]);
    }

    $eval = evaluate(SupportPolicyAgent::class)
        ->prompt('What are the working hours?');

    $eval->assertNotEmpty();
    $eval->assertContains('9 AM to 5 PM');
})->group('deterministic');
