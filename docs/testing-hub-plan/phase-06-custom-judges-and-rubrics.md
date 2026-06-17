# Phase 6: Custom Judges And Rubrics

## Goal

Demonstrate reusable evaluation logic through local custom judges, LLM rubric classes, and built-in LLM judge assertions (supporting both faked/mocked mode and live execution).

## Scope

- Add local custom judges that do not call an AI provider.
- Add rubric classes for live LLM judge examples.
- Add tests for `assertPasses(...)` with custom local judges.
- Cover built-in LLM judge assertions like `assertMeets(...)`, `toMeet(...)`, `assertSimilar(...)`, `assertSimilarTo(...)`, scored rubrics, and custom prompts/instructions.
- Tag tests with the Pest group `judges` (e.g., `->group('judges')`).

## Files To Add Or Update

- `app/Evals/Judges/ContainsAllJudge.php`
- `app/Evals/Judges/StructuredFieldJudge.php`
- `app/Evals/Rubrics/SupportReplyQuality.php`
- `app/Evals/Rubrics/TriageSafety.php`
- `tests/Evals/CustomJudgesTest.php`
- `tests/Evals/LlmJudgeAssertionsTest.php`

## Custom Judge Requirements

`ContainsAllJudge` should:

- accept a list of required terms
- inspect `EvalContext::output`
- return `JudgeResult` with reasoning

`StructuredFieldJudge` should:

- accept expected structured fields
- inspect `EvalContext::result->structured`
- return `JudgeResult` with reasoning

## Rubric Requirements

`SupportReplyQuality` should describe a good customer support reply:

- polite
- empathetic
- clear
- avoids blame
- avoids unsafe promises
- asks for next useful details when needed

`TriageSafety` should describe safe support triage:

- billing sensitivity gets review
- angry or urgent customers get review
- account access/security risk gets review
- feature requests should not be over-escalated

## Plugin APIs Covered

- `assertPasses(Judge)`
- `judge(...)` returning `JudgeResult`
- `assertMeets(new Rubric)`
- `toMeet(new Rubric)`
- `assertMeets(string)`
- `toMeet(string)`
- `assertSimilarTo(...)`
- `toBeSimilarTo(...)`
- `assertSimilar(...)`
- `toBeSimilar(...)`
- `judgeWith(provider, model)`
- `judgeInstructions(...)`

## Acceptance Criteria

- Tests run using faked/mocked LLM data by default (safe for token budgets/CI).
- Setting `RUN_LIVE_EVALS=1` seamlessly switches the suite to run against live LLM providers.
- Local custom judge tests assert on custom judge pass/fail reasoning through `JudgeResult`.
- Rubrics are verified as working and correct.
- All tests belong to the `judges` group.

## Verification

```bash
# Run with faked LLM data (default)
php artisan test --testsuite=evals --group=judges

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals --group=judges
```

## Notes

This phase unifies both local custom judges and live LLM judge assertions into a single testing group. All tests support the dual fake/live toggle.