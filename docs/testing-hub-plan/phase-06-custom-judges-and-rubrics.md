# Phase 6: Custom Judges And Rubrics

## Goal

Demonstrate reusable evaluation logic through local custom judges and LLM rubric classes.

## Scope

- Add local custom judges that do not call an AI provider.
- Add rubric classes for live LLM judge examples.
- Add local tests for `assertPasses(...)`.
- Add opt-in or skipped tests that demonstrate rubric usage without requiring live credentials by default.

## Files To Add Or Update

- `app/Evals/Judges/ContainsAllJudge.php`
- `app/Evals/Judges/StructuredFieldJudge.php`
- `app/Evals/Rubrics/SupportReplyQuality.php`
- `app/Evals/Rubrics/TriageSafety.php`
- `tests/Evals/CustomJudgesTest.php`

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
- `judgeInstructions(...)` if not deferred to Phase 8

## Acceptance Criteria

- Local custom judge tests run without API keys.
- Tests assert on custom judge pass/fail reasoning through `JudgeResult`.
- Rubric classes are available for the live suite.
- Any test that calls a live LLM judge is skipped unless `RUN_LIVE_EVALS=1`.

## Verification

```bash
php artisan test --testsuite=evals-local --filter=CustomJudgesTest
```

## Notes

This phase separates local custom judge behavior from live LLM judge behavior. Rubrics are implementation-ready here, but the main live coverage is Phase 8.
