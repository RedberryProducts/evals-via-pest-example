# Phase 8: Live LLM Smoke Suite

## Goal

Verify the plugin against real LLM judge and agent calls while keeping cost, latency, and variance controlled.

## Scope

- Add `evals-live` test suite.
- Add tests that are skipped unless `RUN_LIVE_EVALS=1`.
- Exercise real `TicketTriageAgent` and `SupportReplyAgent` behavior.
- Exercise built-in LLM judge assertions and provider overrides.

## Files To Add Or Update

- `phpunit.xml`
- `tests/EvalsLive/LlmJudgeAssertionsTest.php`
- `tests/EvalsLive/SupportWorkflowSmokeTest.php`
- `tests/EvalsLive/Pest.php` if suite-specific helpers are useful
- `.env.example` notes for required eval variables

## Environment Requirements

Required:

- `RUN_LIVE_EVALS=1`
- provider key for the configured Laravel AI provider, usually `OPENAI_API_KEY`

Optional:

- `EVALS_JUDGE_PROVIDER`
- `EVALS_JUDGE_MODEL`
- `EVALS_VERBOSE`
- `EVALS_SHOW_REASONING`

## Plugin APIs Covered

- `assertMeets(...)`
- scored `assertMeets(..., threshold: N)`
- `assertDoesNotMeet(...)`
- `assertSimilarTo(...)`
- `assertSimilar(...)`
- `toMeet(...)`
- `toBeSimilarTo(...)`
- `toBeSimilar(...)`
- `judge(...)`
- `judgeWith(...)`
- `judgeInstructions(...)`
- rubric classes with `assertMeets(new Rubric)` and `toMeet(new Rubric)`

## Acceptance Criteria

- The suite is skipped by default without `RUN_LIVE_EVALS=1`.
- Live tests use low sample counts and short prompts.
- Tests document expected provider env vars.
- A failure gives enough context to identify whether the agent, judge, or provider likely failed.

## Verification

```bash
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals-live
```

## Notes

This phase should not block local CI. It is for manual release checks, provider compatibility smoke checks, and demos.
