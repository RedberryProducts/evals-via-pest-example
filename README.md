## Evals Plugin Testing Hub

This repository is a runnable Laravel hub for [`redberry/pest-plugin-evals`](https://github.com/RedberryProducts/pest-plugin-evals).

It demonstrates the plugin's public API with realistic agents, datasets, attachments, judges, sampling, and output/config coverage.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Running Evals

The eval suite is manual-run only and is excluded from automated CI and default PHPUnit/Pest runs.

Run the full suite:

```bash
php artisan test --testsuite=evals
```

Run a single group:

```bash
php artisan test --testsuite=evals --group=attachments
```

Run live LLM evaluations on demand:

```bash
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals
```

Show verbose eval output:

```bash
php artisan test --testsuite=evals --group=tools -- --evals-verbose
```

## Configuration

The published eval config lives in `config/evals.php`.

Key environment variables:

- `EVALS_JUDGE_PROVIDER`
- `EVALS_JUDGE_MODEL`
- `EVALS_VERBOSE`
- `EVALS_SHOW_REASONING`
- `RUN_LIVE_EVALS`

Default test runs use faked LLM responses so the suite stays fast and cheap. Live runs can vary and may incur provider costs.

## Feature Matrix

| Feature area | Primary tests | Notes |
| --- | --- | --- |
| Core `evaluate()` API | `tests/Evals/CoreApiTest.php` | Agent classes, instances, closures, prompt overrides |
| Deterministic assertions | `tests/Evals/DeterministicAssertionsTest.php` | Text, JSON, and array assertions |
| Structured output assertions | `tests/Evals/StructuredOutputAssertionsTest.php` | Nested key and property checks |
| Tool assertions | `tests/Evals/ToolAssertionsTest.php` | Tool usage, counts, sequence, and results |
| Sampling | `tests/Evals/SamplingTest.php` | `samples()`, `repeat()`, and pass rates |
| Custom judges and rubrics | `tests/Evals/CustomJudgesTest.php` | Local judges and rubric-driven assertions |
| LLM judge assertions | `tests/Evals/LlmJudgeAssertionsTest.php` | `assertMeets()`, similarity, and judge results |
| Attachments | `tests/Evals/AttachmentsTest.php` | Prompt attachments and dataset attachment descriptors |
| Output and configuration | `tests/Evals/OutputConfigurationTest.php` | Config defaults, verbose mode, and renderer output |

## Caveats

- Live LLM mode depends on provider credentials and model availability.
- Verbose output is easiest to inspect with `--evals-verbose`.
- Dataset and attachment examples are intentionally small so the hub stays practical to run by hand.

See [`docs/testing-hub-plan.md`](docs/testing-hub-plan.md) for the full phase plan and coverage map.
