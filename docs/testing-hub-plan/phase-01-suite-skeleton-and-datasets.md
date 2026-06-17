# Phase 1: Suite Skeleton And Datasets

## Goal

Create the eval hub test structure without adding new agent behavior yet.

## Scope

- Add `tests/Evals/` as the home for plugin-hub tests.
- Configure a manual-only eval test suite in `phpunit.xml` named `evals` (strictly excluded from automated CI/default execution).
- Add dataset fixture folders.
- Add minimal JSON and XML eval cases that can be loaded by later phases.
- Add a first dataset loader test that does not call an AI provider.
- Tag dataset loader tests with the Pest group `datasets` (e.g., `->group('datasets')`).

## Files To Add Or Update

- `phpunit.xml`
- `tests/Evals/Datasets/support-refund.case.json`
- `tests/Evals/Datasets/support-login.case.json`
- `tests/Evals/Datasets/contact-extraction.case.json`
- `tests/Evals/Datasets/prompt-only-haiku.case.json`
- `tests/Evals/Datasets/support-workflows.case.xml`
- `tests/Evals/DatasetsTest.php`

## Dataset Requirements

JSON cases should cover:

- prompt only
- prompt with expected text
- prompt with expected structured output

XML cases should cover:

- multiple named cases in one file
- at least one case with expected text
- at least one prompt-only case

## Acceptance Criteria

- `EvalCase::fromJson(...)` loads all JSON fixture variants.
- `EvalCase::fromXml(...)` loads named XML cases.
- `EvalCase::fromDirectory(...)` discovers both JSON and XML cases.
- All tests belong to the `datasets` group.
- The `evals` suite is excluded from default/CI runs.

## Verification

```bash
php artisan test --testsuite=evals --group=datasets
```

## Notes

This phase is intentionally small. It establishes the file layout and verifies dataset loading before introducing agents or plugin assertions that execute prompts.