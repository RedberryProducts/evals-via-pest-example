# Phase 4: Tool Assertions

## Goal

Cover every tool assertion with predictable multi-tool workflow behavior.

## Scope

- Add small support workflow tools beyond `CustomerLookupTool`.
- Add `ToolWorkflowAgent` that can call tools in a predictable sequence.
- Add tests for tool matching by class, by name, arguments, closures, sequence, and counts.

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

- Tests verify tool assertions by class and by string name.
- Tests verify exact argument matching and closure-based matching.
- Tests verify a subsequence of expected tools.
- Tests verify exact, minimum, and maximum tool counts.
- Tests verify a no-tool or tool-not-used path.
- No live provider is required.

## Verification

```bash
php artisan test --testsuite=evals-local --filter=ToolAssertionsTest
```

## Notes

Prefer small tools with in-memory data. This keeps test failures focused on plugin tool assertion behavior, not external service behavior.
