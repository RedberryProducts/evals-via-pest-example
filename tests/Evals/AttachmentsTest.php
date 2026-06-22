<?php

use App\Ai\Agents\DocumentReviewAgent;
use Laravel\Ai\Files;
use Redberry\Evals\EvalCase;
use Redberry\Evals\EvalResult;

it('supports fluent attachments, inline prompt attachments, and eval cases', function () {
    fakeAgentResponseIfLiveDisabled(DocumentReviewAgent::class, [
        'The attached refund policy confirms refunds are available within 14 days of purchase.',
        'The attached billing screenshot suggests a duplicate charge that needs review.',
        'The attached refund policy confirms refunds are available within 14 days of purchase.',
    ]);

    $refundPolicy = base_path('tests/Evals/Datasets/attachments/refund-policy.txt');
    $billingScreenshot = base_path('tests/Evals/Datasets/attachments/billing-screenshot.txt');

    $case = EvalCase::make()
        ->prompt('Summarize the attached refund policy.')
        ->attachments([
            Files\Document::fromPath($refundPolicy),
        ])
        ->expected('The attached refund policy confirms refunds are available within 14 days of purchase.');

    $result = evaluate(DocumentReviewAgent::class)
        ->withCase($case)
        ->run();

    expect($result)->toBeInstanceOf(EvalResult::class);
    expect($result->text)->toBe($case->expected);

    evaluate(DocumentReviewAgent::class)
        ->attachments([
            Files\Document::fromPath($billingScreenshot),
        ])
        ->prompt('Review the attached billing screenshot and summarize the issue.')
        ->assertNotEmpty();

    evaluate(DocumentReviewAgent::class)
        ->prompt(
            'Review the attached refund policy and summarize the issue.',
            attachments: [
                Files\Document::fromPath($refundPolicy),
            ],
        )
        ->assertNotEmpty();
})->group('attachments');

it('loads attachment datasets and forwards them through withCase', function () {
    fakeAgentResponseIfLiveDisabled(DocumentReviewAgent::class, [
        'The attached refund policy confirms refunds are available within 14 days and explains the refund window.',
        'The attached refund policy confirms refunds are available within 14 days and explains the refund window.',
        'The general review case is a short support summary with no attachment-specific details.',
    ]);

    $jsonCase = EvalCase::fromJson('tests/Evals/Datasets/document-review.case.json');
    $xmlCases = EvalCase::fromXml('tests/Evals/Datasets/document-workflows.case.xml');

    $jsonResult = evaluate(DocumentReviewAgent::class)
        ->withCase($jsonCase)
        ->run();

    expect($jsonResult)->toBeInstanceOf(EvalResult::class);
    expect($jsonResult->text)->toBe($jsonCase->expected);

    $policyResult = evaluate(DocumentReviewAgent::class)
        ->withCase($xmlCases['policy_summary'])
        ->run();

    expect($policyResult)->toBeInstanceOf(EvalResult::class);
    expect($policyResult->text)->toBe($xmlCases['policy_summary']->expected);

    $generalResult = evaluate(DocumentReviewAgent::class)
        ->withCase($xmlCases['general_review'])
        ->run();

    expect($generalResult)->toBeInstanceOf(EvalResult::class);
    expect($generalResult->text)->toBe($xmlCases['general_review']->expected);
})->group('attachments');
