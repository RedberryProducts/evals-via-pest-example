# Phase 4: Tool Assertions

## Goal

Cover every tool assertion with predictable multi-tool workflow behavior (supporting both faked/mocked mode and live execution).

## Scope

- Add small support workflow tools beyond `CustomerLookupTool`.
- Add `ToolWorkflowAgent` that can call tools in a predictable sequence.
- Add tests for tool matching by class, by name, arguments, closures, sequence, and counts.
- Tag tests with the Pest group `tools` (e.g., `->group('tools')`).

## Files To Add Or Update

- `app/Ai/Agents/ToolWorkflowAgent.php`
- `app/Ai/Tools/BillingHistoryTool.php`
- `app/Ai/Tools/EscalationPolicyTool.php`
- `tests/Evals/ToolAssertionsTest.php`

## Agent Requirements

`ToolWorkflowAgent` should support at least two scenarios:

- Billing/account-sensitive prompt that uses `CustomerLookupTool`, `BillingHistoryTool`, and `EscalationPolicyTool`.
- Feature-request prompt that avoids billing/account tools.

Tool arguments should include stable values such as:

- `customer_identifier`
- `lookup_by`
- `ticket_category`
- `priority`

## Plugin APIs Covered

- `assertToolUsed(ToolClass::class)`
- `assertToolUsed('ToolName')`
- `assertToolUsed(..., exactArrayConstraint)`
- `assertToolUsed(..., closureConstraint)`
- `assertToolNotUsed(...)`
- `assertToolUseSequence([...])`
- `assertToolUsedTimes(...)`
- `assertToolUsedAtLeast(...)`
- `assertToolUsedAtMost(...)`
- `ToolInvocation` argument and result inspection

## Acceptance Criteria

- Tests run using faked/mocked LLM/tool call data by default (safe for token budgets/CI).
- Setting `RUN_LIVE_EVALS=1` seamlessly switches the suite to run against live LLM providers and real tool executions.
- Tests verify tool assertions by class and by string name.
- Tests verify exact argument matching and closure-based matching.
- Tests verify a subsequence of expected tools.
- Tests verify exact, minimum, and maximum tool counts.
- Tests verify a no-tool or tool-not-used path.
- All tests belong to the `tools` group.

## Verification

```bash
# Run with faked LLM data (default)
php artisan test --testsuite=evals --group=tools

# Run with live LLM requests
RUN_LIVE_EVALS=1 php artisan test --testsuite=evals --group=tools
```

## Notes

Prefer small tools with in-memory data. This keeps test failures focused on plugin tool assertion behavior, not external service behavior. All tests support the dual fake/live toggle.