# Phase 2: Core API And Deterministic Assertions

## Goal

Cover `evaluate()` entry forms and deterministic assertions with controlled local responses.

## Scope

- Add `SupportPolicyAgent` for deterministic text and JSON responses.
- Add tests for `evaluate()` with class string, constructor args, agent instance, and closure factory.
- Add tests for `prompt()`, `whenPrompted()`, `run()`, `expected()`, and `withCase()`.
- Add tests for deterministic string, length, JSON, type, equality, and `toBe()` assertions.

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

- Tests run without API keys.
- Tests demonstrate all supported `evaluate()` input styles.
- Deterministic assertions are grouped into readable, focused tests.
- Failures would clearly identify which assertion family regressed.

## Verification

```bash
php artisan test --testsuite=evals-local --filter='CoreApiTest|DeterministicAssertionsTest'
```

## Notes

Keep this phase provider-free. Live provider/model override behavior belongs to Phase 8.
