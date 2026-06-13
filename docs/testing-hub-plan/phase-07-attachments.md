# Phase 7: Attachments

## Goal

Cover prompt attachments and dataset attachment descriptors.

## Scope

- Add small local attachment fixtures.
- Add `DocumentReviewAgent`.
- Add tests for fluent `attachments([...])` and inline prompt attachments.
- Add dataset cases with attachment descriptors.

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

- Tests verify attachment cases load from JSON and XML.
- Tests verify fluent and inline attachment APIs can execute with faked responses.
- Tests do not require real provider file-upload support.

## Verification

```bash
php artisan test --testsuite=evals-local --filter=AttachmentsTest
```

## Notes

Use `.txt` fixtures unless there is a strong reason to add binary files. The plugin needs attachment API coverage; provider-specific media behavior belongs in live/manual docs.
