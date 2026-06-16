# Phase 7: Attachments

## Goal

Cover prompt attachments and dataset attachment descriptors (supporting both faked/mocked mode and live execution).

## Scope

- Add small local attachment fixtures.
- Add `DocumentReviewAgent`.
- Add tests for fluent `attachments([...])` and inline prompt attachments.
- Add dataset cases with attachment descriptors.
- Tag tests with the Pest group `attachments` (e.g., `->group('attachments')`).

## Files To Add Or Update

- `app/Ai/Agents/DocumentReviewAgent.php`
- `tests/Evals/AttachmentsTest.php`
- `tests/Evals/Datasets/document-review.case.json`
- `tests/Evals/Datasets/document-workflows.case.xml`
- `tests/Evals/Datasets/attachments/refund-policy.txt`
- `tests/Evals/Datasets/attachments/billing-screenshot.txt`

## Agent Requirements

`DocumentReviewAgent` should summarize or classify attached support-policy artifacts.

For local tests, responses should be faked so the tests verify plugin attachment plumbing and dataset loading rather than provider file support.

## Plugin APIs Covered

- `attachments([...])`
- `prompt(..., attachments: [...])`
- `EvalCase::attachments([...])`
- JSON dataset attachment descriptors
- XML dataset attachment descriptors
- `withCase(...)` using attachments

## Acceptance Criteria

- Tests run using faked/mocked LLM data by default (safe for token budgets/CI).
- Setting `RUN_LIVE_EVALS=1` seamlessly switches the suite to run against live LLM providers.
- Tests verify attachment cases load from JSON and XML.
- Tests verify fluent and inline attachment APIs can execute with faked responses.
- All tests belong to the `attachments` group.

## Verification

```bash
# Run with faked LLM data (default)
php artisan test --testsuite=evals --group=attachments

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals --group=attachments
```

## Notes

Use `.txt` fixtures unless there is a strong reason to add binary files. The plugin needs attachment API coverage. All tests support the dual fake/live toggle.