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

## Proposed Hub Building Blocks

The detailed implementation plan for each building block lives in the phase documents. At a high level, the hub should use:

| Building block | Purpose | Phase |
| --- | --- | --- |
| Existing support workflow agents | realistic product scenario for triage, replies, and customer lookup | phases 4 and 8 |
| `SupportPolicyAgent` | local deterministic text and JSON assertion coverage | phase 2 |
| `ContactExtractorAgent` | nested structured output assertion coverage | phase 3 |
| `ToolWorkflowAgent` plus support tools | comprehensive tool assertion coverage | phase 4 |
| `VariableReplyAgent` | sampling and minimum-pass examples | phase 5 |
| custom judges and rubrics | local judge coverage and live rubric examples | phase 6 |
| `DocumentReviewAgent` plus fixtures | attachment and dataset attachment coverage | phase 7 |

## Phase Plan

Implementation details are split into small, verifiable phase documents:

- [Phase 1: Suite Skeleton And Datasets](testing-hub-plan/phase-01-suite-skeleton-and-datasets.md)
- [Phase 2: Core API And Deterministic Assertions](testing-hub-plan/phase-02-core-api-and-deterministic-assertions.md)
- [Phase 3: Structured Output Assertions](testing-hub-plan/phase-03-structured-output-assertions.md)
- [Phase 4: Tool Assertions](testing-hub-plan/phase-04-tool-assertions.md)
- [Phase 5: Sampling](testing-hub-plan/phase-05-sampling.md)
- [Phase 6: Custom Judges And Rubrics](testing-hub-plan/phase-06-custom-judges-and-rubrics.md)
- [Phase 7: Attachments](testing-hub-plan/phase-07-attachments.md)
- [Phase 8: Live LLM Smoke Suite](testing-hub-plan/phase-08-live-llm-smoke-suite.md)
- [Phase 9: Output And Documentation](testing-hub-plan/phase-09-output-and-documentation.md)

Each phase should be independently reviewable and should include its own verification command.

## Proposed Test Layout

Detailed file lists are owned by the phase documents. The target layout is:

```text
tests/Evals/
tests/EvalsLive/
tests/Evals/Datasets/
```

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

Start with phases 1 through 4 in code. That will make the repository useful as a real testing hub without requiring API keys. Add the live LLM suite only after the local suite is stable, because live evals are valuable but should remain opt-in due to cost, latency, and provider variance.
