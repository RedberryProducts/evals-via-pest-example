# Phase 8: Output And Documentation

## Goal

Document how to use the hub and verify plugin output/config behavior (supporting both faked/mocked mode and live execution).

## Scope

- Replace the default Laravel README content with project-specific setup, group-based filtering, and execution docs.
- Add a feature matrix mapping plugin APIs to test files.
- Document fake LLM mode (default) and live LLM mode (`RUN_LIVE_EVALS=1`).
- Highlight that the evals suite is strictly manual-run and excluded from automated CI environments.
- Document verbose output checks.
- Add a small process-level smoke check only if it stays simple and reliable.

## Files To Add Or Update

- `README.md`
- `docs/testing-hub-plan.md`
- optional `tests/Evals/OutputConfigurationTest.php`

## Documentation Requirements

README should include:

- project purpose
- setup commands
- unified eval suite command with group filtering
- live LLM toggle variable (`RUN_LIVE_EVALS=1`)
- how to run with `--evals-verbose`
- explicit note that evals are manual-run only and never executed in automated CI (GitHub Actions)
- feature matrix
- expected caveats around LLM cost and variance

## Plugin Behavior Covered

- `config/evals.php` expectations
- `EVALS_JUDGE_PROVIDER`
- `EVALS_JUDGE_MODEL`
- `EVALS_VERBOSE`
- `EVALS_SHOW_REASONING`
- `--evals-verbose`
- output with assertion names, tool usage, scores, reasoning, and sample pass rates

## Acceptance Criteria

- README no longer reads like an untouched Laravel skeleton.
- Users can run the faked eval suite from README instructions instantly.
- Users can toggle live LLM evaluations on-demand using `RUN_LIVE_EVALS=1`.
- Explicit documentation warning against automated CI execution is present.
- Verbose output behavior is either tested or documented with a precise manual command.

## Verification

```bash
# Run the entire faked suite
php artisan test --testsuite=evals


# Run specific groups
php artisan test --testsuite=evals --group=tools

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals
```

Manual verbose output check:

```bash
php artisan test --testsuite=evals --group=tools -- --evals-verbose
```

## Notes

Only add process-level output tests if they are stable in Laravel/Pest. A precise documented smoke command is preferable to a brittle test.