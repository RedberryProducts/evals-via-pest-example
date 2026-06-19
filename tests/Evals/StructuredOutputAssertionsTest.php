<?php

use App\Ai\Agents\ContactExtractorAgent;
use Redberry\Evals\EvalCase;
use Redberry\Evals\EvalResult;

test('structured output assertions support nested data', function () {
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
        ContactExtractorAgent::fake([$expected, $expected]);
    }

    $result = evaluate(ContactExtractorAgent::class)
        ->prompt("Please extract the support triage data from this text: 'My name is John Carter, email is john@example.com, my billing charge looks wrong, and I need someone to review it.'")
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->toArray())->toBe($expected);
    expect($result['customer']['name'])->toBe('John Carter');
    expect($result['ticket']['priority'])->toBe('high');
    expect($result['risk']['level'])->toBe('review_required');

    evaluate(ContactExtractorAgent::class)
        ->prompt("Please extract the support triage data from this text: 'My name is John Carter, email is john@example.com, my billing charge looks wrong, and I need someone to review it.'")
        ->assertArray()
        ->assertHasKeys(['customer.name', 'customer.email', 'ticket.topic', 'ticket.priority', 'risk.level'])
        ->assertHasKey('customer.name', 'John Carter')
        ->assertHasProperty('ticket.topic', 'billing')
        ->assertHasProperties(['customer.email', 'risk.level'])
        ->assertJsonPath('risk.level', 'review_required')
        ->assertMatchesArray($expected)
        ->assertNotEmpty();
})->group('structured');

test('structured output datasets can be loaded and inspected', function () {
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
        ContactExtractorAgent::fake([$expected]);
    }

    $case = EvalCase::fromJson('tests/Evals/Datasets/contact-extraction.case.json');

    $result = evaluate(ContactExtractorAgent::class)
        ->withCase($case)
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->toArray())->toBe($case->expected);
    expect($result['customer']['name'])->toBe('John Carter');
    expect($result['ticket']['topic'])->toBe('billing');
    expect($result['risk']['level'])->toBe('review_required');
})->group('structured');
