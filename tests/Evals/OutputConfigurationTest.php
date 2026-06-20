<?php

use Illuminate\Support\Collection;
use Redberry\Evals\EvalOutputRenderer;
use Redberry\Evals\EvalRecord;
use Redberry\Evals\EvalRecorder;
use Redberry\Evals\Plugin;
use Redberry\Evals\ToolInvocation;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function (): void {
    EvalRecorder::reset();
});

afterEach(function (): void {
    EvalRecorder::reset();
});

it('reads the published eval config defaults', function (): void {
    expect(config('evals.judge.provider'))->toBe('openai');
    expect(config('evals.judge.model'))->toBe('gpt-4o-mini');
    expect(config('evals.judge.default_threshold'))->toBe(80);
    expect(config('evals.output.verbose'))->toBeFalse();
    expect(config('evals.output.show_reasoning'))->toBeTrue();
})->group('output');

it('enables verbose output from the cli flag and environment toggle', function (): void {
    $plugin = new Plugin(new NullOutput);

    $arguments = $plugin->handleArguments(['vendor/bin/pest', '--evals-verbose']);

    expect($arguments)->toBe(['vendor/bin/pest']);
    expect(EvalRecorder::isVerbose())->toBeTrue();

    EvalRecorder::reset();
    $originalVerboseEnv = getenv('EVALS_VERBOSE');
    putenv('EVALS_VERBOSE=yes');

    try {
        $arguments = $plugin->handleArguments(['vendor/bin/pest']);

        expect($arguments)->toBe(['vendor/bin/pest']);
        expect(EvalRecorder::isVerbose())->toBeTrue();
    } finally {
        if ($originalVerboseEnv === false) {
            putenv('EVALS_VERBOSE');
        } else {
            putenv('EVALS_VERBOSE='.$originalVerboseEnv);
        }
    }
})->group('output');

it('renders verbose assertion details, tools, and sample pass rates', function (): void {
    $output = new BufferedOutput;
    $renderer = new EvalOutputRenderer($output);

    $renderer->render([
        new EvalRecord(
            assertionName: 'assertMeets',
            input: 'Summarize the refund policy.',
            output: 'Refunds are available within 14 days of purchase.',
            passed: true,
            toolInvocations: new Collection([
                new ToolInvocation(
                    toolName: 'CustomerLookupTool',
                    toolClass: null,
                    arguments: ['customerId' => 42],
                    result: ['name' => 'Sam'],
                ),
            ]),
            score: 92,
            reasoning: 'The answer is concise and policy-aligned.',
        ),
        new EvalRecord(
            assertionName: 'samples',
            input: 'Summarize the refund policy.',
            output: 'Refunds are available within 14 days of purchase.',
            passed: true,
            toolInvocations: new Collection,
            score: 100,
            reasoning: 'This sample is on policy.',
            sampleIndex: 0,
            sampleTotal: 2,
            sampleMinimum: 1,
        ),
        new EvalRecord(
            assertionName: 'samples',
            input: 'Summarize the refund policy.',
            output: 'Refunds are not available.',
            passed: false,
            toolInvocations: new Collection,
            score: 0,
            reasoning: 'This sample contradicts the refund policy.',
            sampleIndex: 1,
            sampleTotal: 2,
            sampleMinimum: 1,
        ),
    ]);

    $rendered = $output->fetch();

    expect($rendered)->toContain('Assertion: assertMeets');
    expect($rendered)->toContain('Tools: CustomerLookupTool');
    expect($rendered)->toContain('Judge Reasoning:');
    expect($rendered)->toContain('score: 92');
    expect($rendered)->toContain('Assertion: samples');
    expect($rendered)->toContain('Pass Rate: 1/2 (50%)');
})->group('output');
