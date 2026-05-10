# Support Workflow Implementation Plan (Agents + Tools + Instructions)

## Scope

This plan covers implementation work for:

- AI agents
- agent instruction design
- tool implementation
- runtime wiring

Out of scope for this file:

- tests
- UI

## Goal

Implement a two-agent support workflow where:

1. `TicketTriageAgent` converts a customer message into structured ticket data.
2. `SupportReplyAgent` generates a human-facing reply from only the customer message.

Additionally, implement lookup tooling that supports:

- searching users by email or name from an in-memory array
- passing customer identifier through agent constructor
- including identifier context in instructions/tool calls
- fallback to a random customer if no identifier is provided

## Current Baseline

Current codebase has a minimal setup:

- `App\Ai\Agents\SupportAgent`
- `App\Ai\Tools\RemoveUser`

This baseline does not yet support:

- structured triage schema
- dedicated reply agent
- customer lookup tool with identifier routing

## Planned Architecture

```text
Customer message
      |
      | + optional customerIdentifier (constructor arg)
      v
TicketTriageAgent (structured output + tool use)
      |
      v
CustomerLookupTool (array-based lookup)
      |
      v
Structured ticket JSON

Customer message
      v
SupportReplyAgent (text output only)
```

## Implementation Tasks

## 1) Create TicketTriageAgent

Create `app/Ai/Agents/TicketTriageAgent.php`.

### Contracts and traits

- Implement `Laravel\Ai\Contracts\Agent`
- Implement `Laravel\Ai\Contracts\HasTools`
- Implement `Laravel\Ai\Contracts\HasStructuredOutput`
- Use `Laravel\Ai\Promptable`

### Constructor

- Accept optional identifier:
  - `public function __construct(public ?string $customerIdentifier = null) {}`

### Instructions

Define role and decision constraints:

- classify into: `billing | technical | account | feature_request | other`
- classify priority: `low | medium | high | urgent`
- classify sentiment: `neutral | confused | frustrated | angry`
- produce concise summary
- set `needs_human_review`
- require using `CustomerLookupTool` when issue appears billing/account-sensitive
- require forwarding `customerIdentifier` to the tool when available
- allow fallback behavior when identifier not provided

### Structured output schema

Implement `schema(JsonSchema $schema): array` with required keys:

- `category` enum + required
- `priority` enum + required
- `sentiment` enum + required
- `summary` string + required
- `needs_human_review` boolean + required

### Tools

Expose `CustomerLookupTool` from `tools(): iterable`.

## 2) Create SupportReplyAgent

Create `app/Ai/Agents/SupportReplyAgent.php`.

### Contracts and traits

- Implement `Laravel\Ai\Contracts\Agent`
- Use `Laravel\Ai\Promptable`

### Behavior constraints

Instructions must explicitly state:

- input is only the original customer message
- response should be polite, empathetic, and concise
- ask for next useful details when needed
- avoid blame
- avoid unsafe promises (for example, instant refund guarantees)

### Tool policy

- No tools attached for this demo flow
- Keep this agent independent from triage output

## 3) Create CustomerLookupTool

Create `app/Ai/Tools/CustomerLookupTool.php`.

### Contract

- Implement `Laravel\Ai\Contracts\Tool`

### Schema

Implement strict input schema:

- `customer_identifier`: nullable string
- `lookup_by`: enum `auto | email | name` (optional)

### Handle logic

Lookup source is an in-memory array of demo customers.

Matching rules:

1. If identifier looks like email or `lookup_by=email`: exact email match.
2. Else if `lookup_by=name` or `auto`: case-insensitive name matching (contains).
3. If no identifier provided: select random customer.

Return normalized payload (stringified JSON or structured array-compatible output depending on SDK response handling) with:

- `matched` (bool)
- `customer_id`
- `name`
- `email`
- `plan`
- `payment_status`
- `previous_tickets_count`
- `account_state`
- `source_reason` (`explicit_identifier` or `fallback_random`)

## 4) Add Lookup Data Helper (recommended)

Create `app/Ai/Support/CustomerDirectory.php`.

Responsibilities:

- store demo customer array
- provide lookup methods by email/name
- provide fallback random selector
- keep tool class focused and easier to maintain

Notes:

- keep deterministic strategy available for test mode later (seedable random), but do not implement tests in this step

## 5) Constructor Arg and Eval Compatibility

Use eval constructor args pattern (already supported by installed eval package):

```php
evaluate(TicketTriageAgent::class, [
    'customerIdentifier' => 'john@example.com',
]);
```

Implementation requirement:

- TicketTriageAgent must pass constructor-provided identifier into tool-call guidance in instructions

## 6) Instruction Strategy

Keep instructions explicit and reusable.

For `TicketTriageAgent` include:

- classification contract
- escalation/human-review rules
- mandatory tool usage cases
- identifier forwarding requirement

For `SupportReplyAgent` include:

- tone and clarity constraints
- prohibited phrasing (blame)
- no overpromising
- customer-message-only requirement

## 7) Runtime Wiring (non-UI)

Current demo route uses `SupportAgent` directly.

Plan:

- keep current route behavior until new agents are wired
- then replace usage in runtime entrypoint(s) with new agents
- remove or deprecate `SupportAgent` and `RemoveUser` after migration if no longer needed

## File Plan

### New files

- `app/Ai/Agents/TicketTriageAgent.php`
- `app/Ai/Agents/SupportReplyAgent.php`
- `app/Ai/Tools/CustomerLookupTool.php`
- `app/Ai/Support/CustomerDirectory.php` (recommended)

### Existing files likely to update

- `routes/web.php` (wiring update)

## Guardrails

- Keep implementation intentionally demo-friendly, not production-heavy.
- Prefer explicit schemas and deterministic contracts for triage.
- Keep reply agent simple and isolated from triage data.
- Do not add UI or tests in this implementation phase.

## Completion Criteria

Implementation is complete when:

1. Both agents exist and run.
2. Triage agent returns structured output with required fields.
3. Triage agent can use customer lookup tool with constructor-passed identifier.
4. Tool falls back to random customer if identifier is absent.
5. Reply agent generates message from customer text only.
6. Runtime wiring references new agents instead of baseline demo-only setup.
