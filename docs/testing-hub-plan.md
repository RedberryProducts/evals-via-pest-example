# Evals Plugin Testing Hub Plan

## Goal

Turn this Laravel example application into a comprehensive, runnable testing hub for `redberry/pest-plugin-evals`.

The hub should demonstrate every public feature of the plugin with realistic Laravel AI agents, datasets, tools, custom judges, and Pest tests. It should be useful for three audiences:

- package maintainers validating plugin behavior against a real Laravel app
- users learning how to write evals in their own projects
- writers/demo authors who need believable examples rather than toy snippets

## Current State

The app currently contains a support workflow demo:

- `TicketTriageAgent` returns structured support ticket data.
- `SupportReplyAgent` writes a customer-facing reply.
- `CustomerLookupTool` looks up in-memory customer context.
- `/support-demo` renders a UI for running the workflow.
- `tests/Feature/SupportDemoTest.php` verifies controller/UI behavior with faked agents.

The project already requires `redberry/pest-plugin-evals`, but it does not currently contain executable eval tests. Existing eval examples live mostly in `support_workflow_eval_agents.md`.

## Plugin Feature Inventory

The hub should cover these public plugin features.

### Core API

- `evaluate(AgentClass::class)`
- `evaluate(AgentClass::class, constructorArgs: [...])`
- `evaluate(new Agent(...))`
- `evaluate(fn () => Agent::make(...))`
- `prompt(...)`
- `whenPrompted(...)`
- `run()` returning `EvalResult` or `SampleResults`
- lazy execution and chaining

### Prompt Options

- inline `provider`, `model`, and `timeout` overrides on `prompt()`
- fluent `provider()`, `model()`, and `timeout()` overrides
- `attachments([...])`
- inline prompt attachments
- `withCase(EvalCase)`
- `expected(...)`

### LLM Judge Assertions

- `assertMeets(string)`
- `toMeet(string)`
- scored `assertMeets(..., threshold: N)`
- `assertDoesNotMeet(...)`
- `assertSimilarTo(...)`
- `toBeSimilarTo(...)`
- `assertSimilar(...)`
- `toBeSimilar(...)`
- `assertPasses(Judge)`
- `judge(...)` returning `JudgeResult`
- `judgeWith(provider, model)`
- `judgeInstructions(...)`
- rubric classes implementing `Rubric`
- custom judge classes implementing `Judge`

### Deterministic Assertions

- `assertContains(string)`
- `assertContains(array)`
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
- `assertArray()`
- `assertNotEmpty()`
- `assertEquals(...)`
- `assertMatchesArray(...)`
- `toBe(...)` for exact strings and structured arrays

### Structured Output Assertions

- `assertHasKey(...)`
- `assertHasKey(..., value)`
- `assertHasKeys([...])`
- `assertHasProperty(...)`
- `assertHasProperties([...])`
- dot-notation checks for nested structured data
- `EvalResult` array access for structured output

### Tool Assertions

- `assertToolUsed(ToolClass::class)`
- `assertToolUsed('ToolName')`
- exact argument constraints
- closure constraints with `ToolInvocation`
- `assertToolNotUsed(...)`
- `assertToolUseSequence([...])`
- `assertToolUsedTimes(...)`
- `assertToolUsedAtLeast(...)`
- `assertToolUsedAtMost(...)`
- result inspection through `ToolInvocation`

### Sampling

- `samples(count)`
- `samples(count, minimum: N)`
- `repeat(count)` alias
- deterministic assertions over samples
- judge assertions over samples
- `SampleResults::count()`
- `SampleResults::outputs()`
- `SampleResults::first()`
- `SampleResults::last()`
- `SampleResults::judgeResults()`
- `SampleResults::passRate()`
- `SampleResults::passed()`
- iteration over samples

### Datasets

- inline `EvalCase::make()`
- Pest datasets containing `EvalCase` instances
- `EvalCase::fromJson(...)`
- `EvalCase::fromXml(...)`
- `EvalCase::fromDirectory(...)`
- JSON cases with prompt only
- JSON cases with expected text
- JSON cases with expected structured output
- XML cases with multiple named cases
- dataset attachments

### Output And Configuration

- published `config/evals.php`
- `EVALS_JUDGE_PROVIDER`
- `EVALS_JUDGE_MODEL`
- `EVALS_VERBOSE`
- `EVALS_SHOW_REASONING`
- `--evals-verbose` CLI output
- output that includes assertion names, judge reasoning, scores, tool usage, and sample pass rates

## Proposed Test Architecture

Use two suites instead of one monolithic eval suite.

### Suite 1: Local Deterministic Hub

This suite should be safe to run in CI without API keys.

- Use Laravel AI fakes where possible.
- Test plugin chaining and assertions against controlled responses.
- Exercise deterministic, structured, tool, dataset, sampling, and result API features.
- Avoid LLM judge calls unless the judge itself is faked or replaced with a custom local judge.

Suggested command:

```bash
php artisan test --testsuite=evals-local
```

### Suite 2: Live LLM Smoke Hub

This suite should require provider credentials and be opt-in.

- Use real `TicketTriageAgent` and `SupportReplyAgent` calls.
- Cover `assertMeets`, scored judges, similarity checks, custom rubric prompts, and `judgeWith()`.
- Keep tests small and low-count to control cost.
- Skip when required provider env vars are absent.

Suggested command:

```bash
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals-live
```

## Proposed Agents And Fixtures

### Keep Existing Agents

Use the current support workflow as the main realistic product scenario.

- `TicketTriageAgent`: structured output, tool usage, constructor args, JSON-path assertions, human-review classification.
- `SupportReplyAgent`: text quality, deterministic content checks, similarity, rubrics, negative criteria.
- `CustomerLookupTool`: tool class/name matching, arguments, results, counts.

### Add `SupportPolicyAgent`

Purpose: deterministic text and JSON assertions.

Behavior:

- answers support policy questions
- can return plain text or JSON depending on prompt
- mentions known policy terms like refund window, escalation, plan, and billing

Covers:

- string containment
- regex
- length checks
- JSON validity
- JSON path checks
- JSON structure checks
- exact text checks where faked

### Add `ContactExtractorAgent`

Purpose: structured output assertions beyond the current triage shape.

Behavior:

- extracts nested customer/contact data from text
- returns nested structured output such as `customer.name`, `customer.email`, `ticket.topic`, and `risk.level`

Covers:

- `assertArray()`
- `assertHasKey()` with dot notation
- `assertHasKeys()`
- `assertHasProperty()` aliases
- `assertMatchesArray()`
- `toBe([...])`
- `EvalResult` array access

### Add `ToolWorkflowAgent`

Purpose: comprehensive tool assertion coverage.

Behavior:

- uses multiple small tools in predictable order for account-sensitive tickets
- likely tools: `CustomerLookupTool`, `BillingHistoryTool`, `EscalationPolicyTool`
- returns a short triage or action plan

Covers:

- tool class matching
- tool name matching
- exact arguments
- closure constraints
- sequence checks
- used times / at least / at most
- tool result inspection

### Add `DocumentReviewAgent`

Purpose: attachment examples and dataset attachment coverage.

Behavior:

- summarizes a small local text/PDF-like fixture or image-like fixture through Laravel AI file attachments
- can be faked in local suite and run live only when configured

Covers:

- `attachments([...])`
- inline prompt attachments
- JSON/XML dataset attachment descriptors

### Add `VariableReplyAgent`

Purpose: sampling and minimum-pass examples.

Behavior:

- produces small variations of support replies
- live suite demonstrates realistic LLM variance
- local suite can fake multiple responses

Covers:

- `samples()`
- `repeat()`
- minimum pass counts
- `SampleResults` API
- sampled judge output

### Add Custom Rubrics And Judges

Suggested rubrics:

- `SupportReplyQuality`: polite, clear, empathetic, avoids unsafe promises.
- `TriageSafety`: flags billing, anger, account access, refund, and security issues for human review.

Suggested local custom judges:

- `ContainsAllJudge`: passes when output contains required terms.
- `StructuredFieldJudge`: passes when structured output contains expected fields.

Covers:

- `Rubric`
- `Judge`
- `assertPasses()`
- `assertMeets(new Rubric)`
- `toMeet(new Rubric)`
- `judge()` result inspection without relying only on built-in judge examples

## Proposed Test Files

### `tests/Evals/CoreApiTest.php`

Covers `evaluate()` entry forms, `prompt()`, `whenPrompted()`, `run()`, constructor arguments, agent instance, closure factory, lazy execution, and fluent chaining.

### `tests/Evals/DeterministicAssertionsTest.php`

Covers all string, length, JSON, type, equality, and `toBe()` deterministic assertions.

### `tests/Evals/StructuredOutputAssertionsTest.php`

Covers structured output keys, aliases, nested paths, array matching, and `EvalResult` array access.

### `tests/Evals/ToolAssertionsTest.php`

Covers every tool assertion method with predictable tool invocations.

### `tests/Evals/LlmJudgeAssertionsTest.php`

Opt-in live suite for built-in judge assertions, similarity, negation, judge result inspection, judge provider overrides, and custom judge instructions.

### `tests/Evals/CustomJudgesTest.php`

Covers custom `Rubric` and custom `Judge` examples.

### `tests/Evals/SamplingTest.php`

Covers `samples`, `repeat`, minimum pass counts, deterministic checks over samples, judge checks over samples, and `SampleResults` APIs.

### `tests/Evals/DatasetsTest.php`

Covers inline `EvalCase`, Pest datasets, JSON files, XML files, directory discovery, expected values, structured expected values, and attachments.

### `tests/Evals/OutputConfigurationTest.php`

Covers config expectations and provides documented commands for `--evals-verbose`, `EVALS_VERBOSE`, and `EVALS_SHOW_REASONING`. This may need a process-level test or a documented manual smoke check because plugin output is produced by Pest event subscribers.

## Proposed Dataset Layout

```text
tests/Evals/Datasets/
  support-refund.case.json
  support-login.case.json
  contact-extraction.case.json
  prompt-only-haiku.case.json
  support-workflows.case.xml
  attachments/
    refund-policy.txt
    billing-screenshot.txt
```

## Implementation Phases

### Phase 1: Hub Skeleton

- Add `tests/Evals` suite configuration.
- Add dataset directory and small fixtures.
- Add local-only helper agents/judges needed for deterministic coverage.
- Add tests for core API, deterministic assertions, structured output, datasets, sampling result APIs, and local custom judges.

### Phase 2: Tool And Workflow Coverage

- Expand support tools beyond `CustomerLookupTool`.
- Add `ToolWorkflowAgent`.
- Add tests for every tool assertion, including constraints and sequences.
- Add tests that use the existing support demo agents with constructor args.

### Phase 3: Live LLM Smoke Coverage

- Add `RUN_LIVE_EVALS` guard.
- Add small live tests for judge assertions, similarity, rubrics, `judgeWith()`, and `judgeInstructions()`.
- Document required env vars and expected cost/time.

### Phase 4: Output And Documentation

- Add README instructions for running local and live eval suites.
- Add a short feature matrix mapping plugin APIs to test files.
- Add manual or process-level smoke checks for `--evals-verbose` output.

## Suggested Feature Matrix

| Feature area | Primary tests | Required app additions |
| --- | --- | --- |
| Core `evaluate()` API | `CoreApiTest` | none or helper fake agent |
| Prompt overrides | `CoreApiTest` | provider/model-safe agents |
| Attachments | `DatasetsTest`, `DocumentReviewAgentTest` | `DocumentReviewAgent`, fixture files |
| Deterministic assertions | `DeterministicAssertionsTest` | `SupportPolicyAgent` |
| Structured assertions | `StructuredOutputAssertionsTest` | `ContactExtractorAgent` |
| Tool assertions | `ToolAssertionsTest` | `ToolWorkflowAgent`, extra tools |
| Judge assertions | `LlmJudgeAssertionsTest` | existing reply/triage agents, rubrics |
| Custom judges/rubrics | `CustomJudgesTest` | `SupportReplyQuality`, local judges |
| Sampling | `SamplingTest` | `VariableReplyAgent` or faked responses |
| Datasets | `DatasetsTest` | JSON/XML cases and attachments |
| CLI/config output | `OutputConfigurationTest` and docs | test commands, config notes |

## Open Questions

- Should local eval tests use Laravel AI fakes exclusively, or should some local tests use deterministic custom agents that never call a provider?
- Should live eval tests target OpenAI only, or support any provider through `EVALS_JUDGE_PROVIDER` and `EVALS_JUDGE_MODEL`?
- Should the hub pin model names in agents, or move demo model configuration into env variables to keep examples current?
- Should this repo become an example app only, or also a compatibility test fixture consumed by the plugin repository CI?

## Recommendation

Start with Phase 1 and Phase 2 in code. That will make the repository useful as a real testing hub without requiring API keys. Add Phase 3 after the local suite is stable, because live LLM evals are valuable but should remain opt-in due to cost, latency, and provider variance.
