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

Use a single unified evaluation suite (`evals`) designed for manual, opt-in execution only. This suite is strictly excluded from automated CI/GitHub Actions to prevent unexpected costs and token drain.

### Single Unified Suite: Evals Hub

Every phase test is written using real LLM-backed requests and assertions because AI agents cannot be truly evaluated without an LLM involved (even deterministic assertions require some initial LLM output to assert against).

To support development and budget management, the test suite supports two execution modes:
1. **Fake/Mock Mode (Default):** Runs assertions against faked/recorded LLM outputs so developers can verify test logic, plugin plumbing, and assertions without burning API tokens.
2. **Live LLM Mode:** Toggled on via an environment variable (`RUN_LIVE_EVALS=1`) to run assertions against live LLM model calls once implementation is completed.

### Group-Based Filtering

To prevent wasting tokens on running the entire suite, each phase's tests are tagged with a dedicated Pest group. This lets developers run only specific suites of interest:

```bash
# Run only tool assertions tests
php artisan test --testsuite=evals --group=tools

# Run only sampling tests
php artisan test --testsuite=evals --group=sampling
```

### CI and Manual Run Policy

The evals suite is manual-run only. It must not run in automated CI environments (like GitHub Actions):
- It is configured under a separate `<testsuite>` in `phpunit.xml` that is not part of default PHPUnit/Pest suite execution.
- It requires manual invocation and explicit opt-in for live LLM mode.

## Proposed Hub Building Blocks

The detailed implementation plan for each building block lives in the phase documents. At a high level, the hub should use:

| Building block | Purpose | Phase |
| --- | --- | --- |
| Existing support workflow agents | realistic product scenario for triage, replies, and customer lookup | phase 4 and 6 |
| `SupportPolicyAgent` | local deterministic text and JSON assertion coverage | phase 2 |
| `ContactExtractorAgent` | nested structured output assertion coverage | phase 3 |
| `ToolWorkflowAgent` plus support tools | comprehensive tool assertion coverage | phase 4 |
| `VariableReplyAgent` | sampling and minimum-pass examples | phase 5 |
| custom judges and rubrics | local judge coverage and live rubric examples | phase 6 |
| `DocumentReviewAgent` plus fixtures | attachment and dataset attachment coverage | phase 7 |

## Agent Additions & Architecture

Are the existing agents (`TicketTriageAgent` and `SupportReplyAgent`) enough to write all these test suites?

While we could theoretically reuse the two existing agents with mock configurations for all tests, doing so would make the test suites highly convoluted, heavily coupled, and difficult to understand. 

To provide clean, self-contained, and realistic examples for each plugin feature (such as nested structured output, tool sequence verification, multiple varied outputs for sampling, and document attachments), the plan introduces dedicated, lightweight, single-purpose agents in their respective phases:
- **`SupportPolicyAgent` (Phase 2):** Simple agent for plain-text and JSON deterministic responses.
- **`ContactExtractorAgent` (Phase 3):** Specifically designed to return a nested schema for structured output assertions.
- **`ToolWorkflowAgent` (Phase 4):** Predictably invokes multiple tools to cover sequencing and count assertions.
- **`VariableReplyAgent` (Phase 5):** Designed with faked responses of varying outcomes to demonstrate sampling and pass-rate thresholds.
- **`DocumentReviewAgent` (Phase 7):** Handles document attachment reading and summarization.

Each of these agents is introduced incrementally alongside its relevant test suite to keep the codebase modular, realistic, and highly educational.

## Phase Plan

Implementation details are split into small, verifiable phase documents:

- [Phase 1: Suite Skeleton And Datasets](testing-hub-plan/phase-01-suite-skeleton-and-datasets.md)
- [Phase 2: Core API And Deterministic Assertions](testing-hub-plan/phase-02-core-api-and-deterministic-assertions.md)
- [Phase 3: Structured Output Assertions](testing-hub-plan/phase-03-structured-output-assertions.md)
- [Phase 4: Tool Assertions](testing-hub-plan/phase-04-tool-assertions.md)
- [Phase 5: Sampling](testing-hub-plan/phase-05-sampling.md)
- [Phase 6: Custom Judges And Rubrics](testing-hub-plan/phase-06-custom-judges-and-rubrics.md)
- [Phase 7: Attachments](testing-hub-plan/phase-07-attachments.md)
- [Phase 8: Output And Documentation](testing-hub-plan/phase-08-output-and-documentation.md)

Each phase should be independently reviewable and should include its own verification command.

## Proposed Test Layout

Detailed file lists are owned by the phase documents. The target layout is:

```text
tests/Evals/
tests/Evals/Datasets/
```

## Suggested Feature Matrix

| Feature area | Primary tests | Required app additions |
| --- | --- | --- |
| Core `evaluate()` API | `CoreApiTest` | none or helper fake agent |
| Prompt overrides | `CoreApiTest` | provider/model-safe agents |
| Attachments | `DatasetsTest`, `AttachmentsTest` | `DocumentReviewAgent`, fixture files |
| Deterministic assertions | `DeterministicAssertionsTest` | `SupportPolicyAgent` |
| Structured assertions | `StructuredOutputAssertionsTest` | `ContactExtractorAgent` |
| Tool assertions | `ToolAssertionsTest` | `ToolWorkflowAgent`, extra tools |
| Judge assertions | `LlmJudgeAssertionsTest` | existing reply/triage agents, rubrics |
| Custom judges/rubrics | `CustomJudgesTest` | `SupportReplyQuality`, local judges |
| Sampling | `SamplingTest` | `VariableReplyAgent` or faked responses |
| Datasets | `DatasetsTest` | JSON/XML cases and attachments |
| CLI/config output | `OutputConfigurationTest` and docs | test commands, config notes |

## Open Questions

- Should the hub pin model names in agents, or move demo model configuration into env variables to keep examples current?
- Should this repo become an example app only, or also a compatibility test fixture consumed by the plugin repository CI?

## Recommendation

Implement all phases with a default faked/mocked toggle so developers can run checks instantly without spending tokens. Real LLM requests can be run on-demand by setting `RUN_LIVE_EVALS=1`.