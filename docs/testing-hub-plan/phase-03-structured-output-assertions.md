# Phase 3: Structured Output Assertions

## Goal

Cover structured output assertions using nested, realistic support data (supporting both faked/mocked mode and live execution).

## Scope

- Add `ContactExtractorAgent`.
- Add tests for structured array output and nested paths.
- Reuse the `contact-extraction.case.json` dataset from Phase 1.
- Verify `EvalResult` array access for structured output.
- Tag tests with the Pest group `structured` (e.g., `->group('structured')`).

## Files To Add Or Update

- `app/Ai/Agents/ContactExtractorAgent.php`
- `tests/Evals/StructuredOutputAssertionsTest.php`
- `tests/Evals/Datasets/contact-extraction.case.json`

## Agent Requirements

`ContactExtractorAgent` should return structured data shaped like:

```json
{
  "customer": {
    "name": "John Carter",
    "email": "john@example.com"
  },
  "ticket": {
    "topic": "billing",
    "priority": "high"
  },
  "risk": {
    "level": "review_required"
  }
}
```

## Plugin APIs Covered

- `assertArray()`
- `assertHasKey(...)`
- `assertHasKey(..., value)`
- `assertHasKeys([...])`
- `assertHasProperty(...)`
- `assertHasProperties([...])`
- `assertJsonPath(...)` against structured output
- `assertMatchesArray(...)`
- `toBe([...])`
- `EvalResult` array access

## Acceptance Criteria

- Tests run using faked/mocked LLM data by default (safe for token budgets/CI).
- Setting `RUN_LIVE_EVALS=1` seamlessly switches the suite to run against live LLM providers.
- Tests validate top-level and dot-notation nested keys.
- Tests validate exact values for at least one nested field.
- Tests show both fluent assertions and native Pest `expect()` against `run()` output.
- All tests belong to the `structured` group.

## Verification

```bash
# Run with faked LLM data (default)
php artisan test --testsuite=evals --group=structured

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals --group=structured
```

## Notes

This phase should not add tool behavior. Tool output and invocation checks belong to Phase 4. All tests support the dual fake/live toggle.