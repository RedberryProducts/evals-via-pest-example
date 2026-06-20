<?php

use App\Ai\Agents\SupportPolicyAgent;
use App\Ai\Agents\SupportReplyAgent;
use App\Evals\Rubrics\SupportReplyQuality;
use App\Evals\Rubrics\TriageSafety;
use Laravel\Ai\StructuredAnonymousAgent;
use Redberry\Evals\JudgeResult;

it('supports binary rubric assertions and raw judge results', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportReplyAgent::fake([
            'I am sorry about the issue. Please send the transaction ID and payment email so we can investigate.',
            'I am sorry about the issue. Please send the transaction ID and payment email so we can investigate.',
            'I am sorry about the issue. Please send the transaction ID and payment email so we can investigate.',
        ])->preventStrayPrompts();

        StructuredAnonymousAgent::fake([
            ['passed' => true, 'reasoning' => 'The reply is empathetic and asks for the next useful detail.'],
            ['passed' => true, 'reasoning' => 'The reply is empathetic and asks for the next useful detail.'],
            ['score' => 91, 'reasoning' => 'The reply shows good customer reassurance and safety handling.'],
        ])->preventStrayPrompts();
    }

    evaluate(SupportReplyAgent::class)
        ->prompt('My order was charged twice and I need help.')
        ->judgeWith('openai', 'gpt-5-nano')
        ->judgeInstructions('Treat payment safety and customer reassurance as the top priority.')
        ->assertMeets(new SupportReplyQuality);

    evaluate(SupportReplyAgent::class)
        ->prompt('My order was charged twice and I need help.')
        ->judgeWith('openai', 'gpt-5-nano')
        ->judgeInstructions('Treat payment safety and customer reassurance as the top priority.')
        ->toMeet(new SupportReplyQuality);

    $judgeResult = evaluate(SupportReplyAgent::class)
        ->prompt('My order was charged twice and I need help.')
        ->judgeWith('openai', 'gpt-5-nano')
        ->judgeInstructions('Treat payment safety and customer reassurance as the top priority.')
        ->judge('The response should reassure the customer and ask for payment details.', new TriageSafety);

    expect($judgeResult)->toBeInstanceOf(JudgeResult::class);
    expect($judgeResult->passed)->toBeTrue();
    expect($judgeResult->score)->toBeGreaterThanOrEqual(80);
    expect($judgeResult->reasoning)->toContain('customer reassurance');
})->group('judges');

it('supports similarity aliases with expected values and custom judge settings', function () {
    if (! env('RUN_LIVE_EVALS')) {
        SupportPolicyAgent::fake([
            'Our refund policy allows a full refund within 14 days of purchase.',
            'Our refund policy allows a full refund within 14 days of purchase.',
            'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
            'Our support hours are 9 AM to 5 PM EST, Monday through Friday.',
        ])->preventStrayPrompts();

        StructuredAnonymousAgent::fake([
            ['score' => 94, 'reasoning' => 'Semantically equivalent to the expected refund policy text.'],
            ['score' => 93, 'reasoning' => 'Semantically equivalent to the expected refund policy text.'],
            ['score' => 91, 'reasoning' => 'Semantically equivalent to the expected support hours text.'],
            ['score' => 92, 'reasoning' => 'Semantically equivalent to the expected support hours text.'],
        ])->preventStrayPrompts();
    }

    evaluate(SupportPolicyAgent::class)
        ->prompt('Summarize the refund policy in one sentence.')
        ->judgeWith('openai', 'gpt-5-nano')
        ->assertSimilarTo('Refunds are available within 14 days of purchase.');

    evaluate(SupportPolicyAgent::class)
        ->prompt('Summarize the refund policy in one sentence.')
        ->judgeWith('openai', 'gpt-5-nano')
        ->toBeSimilarTo('Refunds are available within 14 days of purchase.');

    evaluate(SupportPolicyAgent::class)
        ->expected('Our support hours are 9 AM to 5 PM EST, Monday through Friday.')
        ->prompt('What are the support hours?')
        ->judgeWith('openai', 'gpt-5-nano')
        ->assertSimilar();

    evaluate(SupportPolicyAgent::class)
        ->expected('Our support hours are 9 AM to 5 PM EST, Monday through Friday.')
        ->prompt('What are the support hours?')
        ->judgeWith('openai', 'gpt-5-nano')
        ->toBeSimilar();
})->group('judges');
