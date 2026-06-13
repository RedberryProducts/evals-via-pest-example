# Phase 5: Sampling

## Goal

Cover repeated eval execution and `SampleResults` behavior without live model variance.

## Scope

- Add `VariableReplyAgent` or use Laravel AI fakes with multiple queued responses.
- Add tests for `samples()`, `repeat()`, minimum pass counts, sampled deterministic assertions, and sampled custom judge assertions.
- Add tests for `SampleResults` accessors.

## Files To Add Or Update

- `app/Ai/Agents/VariableReplyAgent.php`
- `tests/Evals/SamplingTest.php`

## Agent Requirements

`VariableReplyAgent` should return short support replies that vary safely but preserve required policy terms.

Local tests may fake responses to make pass/fail counts deterministic.

## Plugin APIs Covered

- `samples(count)`
- `samples(count, minimum: N)`
- `repeat(count)`
- sampled deterministic assertions
- sampled `assertPasses(...)` with a local custom judge
- `SampleResults::count()`
- `SampleResults::outputs()`
- `SampleResults::first()`
- `SampleResults::last()`
- `SampleResults::judgeResults()`
- `SampleResults::passRate()`
- `SampleResults::passed()`
- iteration over `SampleResults`

## Acceptance Criteria

- Tests prove all samples pass when no minimum is supplied.
- Tests prove a minimum pass threshold can allow controlled variance.
- Tests inspect `SampleResults` returned by `run()`.
- Tests inspect `SampleResults` returned by `judge()` or `assertPasses()` where applicable.
- No live provider is required.

## Verification

```bash
php artisan test --testsuite=evals-local --filter=SamplingTest
```

## Notes

Keep sample counts low, usually 2 or 3. The goal is feature coverage, not statistical confidence.
