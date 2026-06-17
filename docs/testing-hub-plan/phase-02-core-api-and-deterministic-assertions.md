# Phase 2: Core API And Deterministic Assertions

## Goal

Cover `evaluate()` entry forms and deterministic assertions with real agent and LLM-backed execution (supporting both faked/mocked mode and live execution).

## Scope

- Add `SupportPolicyAgent` for deterministic text and JSON responses.
- Add tests for `evaluate()` with class string, constructor args, agent instance, and closure factory.
- Add tests for `prompt()`, `whenPrompted()`, `run()`, `expected()`, and `withCase()`.
- Add tests for deterministic string, length, JSON, type, equality, and `toBe()` assertions.
- Tag tests with the Pest group `deterministic` (e.g., `->group('deterministic')`).

## Files To Add Or Update

- `app/Ai/Agents/SupportPolicyAgent.php`
- `tests/Evals/CoreApiTest.php`
- `tests/Evals/DeterministicAssertionsTest.php`

## Agent Requirements

`SupportPolicyAgent` should support deterministic local testing through Laravel AI fakes or a predictable test-mode response strategy.

It should produce examples for:

- plain text support policy answers
- JSON support policy answers
- exact short answers for equality checks
- longer answers for length checks

## Plugin APIs Covered

- `evaluate(AgentClass::class)`
- `evaluate(AgentClass::class, [...])`
- `evaluate(new Agent)`
- `evaluate(fn () => new Agent)`
- `prompt(...)`
- `whenPrompted(...)`
- `run()`
- `withCase(...)`
- `expected(...)`
- `assertContains(...)`
- `assertContainsAny(...)`
- `assertNotContains(...)`
- `assertMatches(...)`
- `assertLengthLessThan(...)`
- `assertLengthGreaterThan(...)`
- `assertLengthBetween(...)`
- `assertJson()`
- `assertJsonPath(...)`
- `assertJsonStructure(...)`
- `assertString()`
- `assertNotEmpty()`
- `assertEquals(...)`
- `toBe(...)`

## Acceptance Criteria

- Tests run using faked/mocked LLM data by default (safe for token budgets/CI).
- Setting `RUN_LIVE_EVALS=1` seamlessly switches the suite to run against live LLM providers.
- Tests demonstrate all supported `evaluate()` input styles.
- All tests belong to the `deterministic` group.

## Verification

```bash
# Run with faked LLM data (default)
php artisan test --testsuite=evals --group=deterministic

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals --group=deterministic
```

## Notes

All phases now support dual-mode execution (fake/live LLM toggles). This ensures developers can run, test, and release quickly while preserving the ability to run live end-to-end evaluations on-demand.