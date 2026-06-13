# Phase 9: Output And Documentation

## Goal

Document how to use the hub and verify plugin output/config behavior.

## Scope

- Replace the default Laravel README content with project-specific setup and usage docs.
- Add a feature matrix mapping plugin APIs to test files.
- Document local and live eval commands.
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
- local eval suite command
- live eval suite command
- required live env vars
- how to run `--evals-verbose`
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
- Users can run the local suite from README instructions.
- Users can opt into live tests from README instructions.
- Verbose output behavior is either tested or documented with a precise manual command.

## Verification

```bash
php artisan test --testsuite=evals-local
```

Manual verbose output check:

```bash
php artisan test --testsuite=evals-local --filter=ToolAssertionsTest -- --evals-verbose
```

## Notes

Only add process-level output tests if they are stable in Laravel/Pest. A precise documented smoke command is preferable to a brittle test.
