<?php

use Redberry\Evals\EvalCase;

it('loads prompt-only JSON cases', function () {
    $case = EvalCase::fromJson('tests/Evals/Datasets/prompt-only-haiku.case.json');

    expect($case)->toBeInstanceOf(EvalCase::class);
    expect($case->prompt)->toBe('Write a short haiku about a software bug.');
    expect($case->expected)->toBeNull();
})->group('datasets');

it('loads prompt-with-expected-text JSON cases', function () {
    $case = EvalCase::fromJson('tests/Evals/Datasets/support-refund.case.json');

    expect($case)->toBeInstanceOf(EvalCase::class);
    expect($case->prompt)->toBe("I requested a refund for my subscription 3 days ago, but I haven't seen it in my bank account yet. Can you check?");
    expect($case->expected)->toBe('Refunds typically take 5-10 business days to appear in your bank account depending on your financial institution.');
})->group('datasets');

it('loads prompt-with-expected-structured-output JSON cases', function () {
    $case = EvalCase::fromJson('tests/Evals/Datasets/contact-extraction.case.json');

    expect($case)->toBeInstanceOf(EvalCase::class);
    expect($case->prompt)->toBe("Please extract the support triage data from this text: 'My name is John Carter, email is john@example.com, my billing charge looks wrong, and I need someone to review it.'");
    expect($case->expected)->toBeArray();
    expect($case->expected['customer']['name'])->toBe('John Carter');
    expect($case->expected['customer']['email'])->toBe('john@example.com');
    expect($case->expected['ticket']['topic'])->toBe('billing');
    expect($case->expected['ticket']['priority'])->toBe('high');
    expect($case->expected['risk']['level'])->toBe('review_required');
})->group('datasets');

it('loads named XML cases', function () {
    $cases = EvalCase::fromXml('tests/Evals/Datasets/support-workflows.case.xml');

    expect($cases)->toBeArray();
    expect($cases)->toHaveCount(2);
    expect($cases)->toHaveKeys(['refund_request', 'general_query']);

    $refundCase = $cases['refund_request'];
    expect($refundCase)->toBeInstanceOf(EvalCase::class);
    expect($refundCase->prompt)->toBe('I want a refund for my order.');
    expect($refundCase->expected)->toBe('Please contact our support team to request a refund.');

    $queryCase = $cases['general_query'];
    expect($queryCase)->toBeInstanceOf(EvalCase::class);
    expect($queryCase->prompt)->toBe('What are your operating hours?');
    expect($queryCase->expected)->toBeNull();
})->group('datasets');

it('auto-discovers both JSON and XML cases from a directory', function () {
    $cases = EvalCase::fromDirectory('tests/Evals/Datasets');

    expect($cases)->toBeArray();
    expect($cases)->toHaveCount(9);
    expect($cases)->toHaveKeys([
        'prompt-only-haiku',
        'support-refund',
        'support-login',
        'contact-extraction',
        'document-review',
        'refund_request',
        'general_query',
        'policy_summary',
        'general_review',
    ]);
})->group('datasets');
