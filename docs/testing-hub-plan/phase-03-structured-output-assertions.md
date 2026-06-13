# Phase 3: Structured Output Assertions

## Goal

Cover structured output assertions using nested, realistic support data.

## Scope

- Add `ContactExtractorAgent`.
- Add tests for structured array output and nested paths.
- Reuse the `contact-extraction.case.json` dataset from Phase 1.
- Verify `EvalResult` array access for structured output.

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

- Tests validate top-level and dot-notation nested keys.
- Tests validate exact values for at least one nested field.
- Tests show both fluent assertions and native Pest `expect()` against `run()` output.
- No live provider is required.

## Verification

```bash
php artisan test --testsuite=evals-local --filter=StructuredOutputAssertionsTest
```

## Notes

This phase should not add tool behavior. Tool output and invocation checks belong to Phase 4.
