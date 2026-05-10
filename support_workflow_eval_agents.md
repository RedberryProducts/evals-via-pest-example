# Use case: AI-powered support workflow

We will use a simple customer support workflow with **two agents**.

```text
Customer message
      ↓
TicketTriageAgent
      ↓
Structured ticket data

Customer message
      ↓
SupportReplyAgent
      ↓
Suggested reply
```

The important change: **the reply agent uses only the customer message**.

This keeps the demo simple and easy to understand:

> One agent turns a customer message into structured data.  
> The other agent turns a customer message into a helpful human reply.

We are not building a full production workflow here. We are using two focused agents to showcase three practical ways of using evals:

```text
→ Create tests with tool use checks
→ Implement structured output validation
→ Add criteria checks with custom rubrics
```

---

# Agent 1: TicketTriageAgent

## Description

`TicketTriageAgent` receives a raw customer support message and converts it into structured ticket data.

It should decide:

```text
- What kind of issue is this?
- How urgent is it?
- What is the customer’s mood?
- Does a human need to review it?
- What is the short summary?
```

Example customer message:

```text
Payment failed, but money was taken from my card.
```

Expected structured output:

```json
{
  "category": "billing",
  "priority": "high",
  "sentiment": "frustrated",
  "summary": "Customer reports a failed payment while their card was charged.",
  "needs_human_review": true
}
```

Recommended output fields:

```text
category:
billing | technical | account | feature_request | other

priority:
low | medium | high | urgent

sentiment:
neutral | confused | frustrated | angry

summary:
short plain-English summary

needs_human_review:
true | false
```

## Why this agent is useful for the demo

This agent is perfect for showing **structured output validation**.

We should not use `toMeet()` as the main validation method here, because the agent’s job is not to write beautiful text. Its job is to return predictable machine-readable data.

---

# Agent 2: SupportReplyAgent

## Description

`SupportReplyAgent` receives only the original customer message and generates a suggested support reply.

It does not receive triage data.

Example customer message:

```text
Payment failed, but money was taken from my card.
```

Expected reply:

```text
Hi, sorry about this. I understand how frustrating it is to see a charge after a failed payment. Please send us the transaction ID or the email used for the payment, and our team will review it as soon as possible.
```

# Test type 1: Tool use checks

## Goal

Show that evals can check **how an agent worked**, not only what it returned.

For example, some billing or account-related tickets require customer context. The triage agent should not guess from the message alone.

## Example tool

```php
CustomerLookupTool
```

This tool could check:

```text
- customer plan
- payment status
- previous tickets
- account state
```

## Test: TicketTriageAgent uses customer lookup

```php
use App\Ai\Agents\TicketTriageAgent;
use App\Ai\Tools\CustomerLookupTool;

it('checks customer account before triaging plan mismatch', function () {
    evaluate(TicketTriageAgent::class)
        ->whenPrompted('I paid for Pro but my account still shows Free.')
        ->assertToolUsed(CustomerLookupTool::class)
        ->assertJsonPath('category', 'billing')
        ->assertJsonPath('priority', 'high')
        ->assertJsonPath('needs_human_review', true);
});
```

## Speaker line

> In agent systems, the path matters.  
> A good final output is not enough if the agent skipped the tool it was supposed to use.

---

# Test type 2: Structured output validation

## Goal

Show that evals can validate machine-readable agent output.

This is important when an agent powers a product UI, automation, or backend workflow.

This section belongs to `TicketTriageAgent`.

## Test: required fields exist

```php
use App\Ai\Agents\TicketTriageAgent;

it('returns required triage fields', function () {
    evaluate(TicketTriageAgent::class)
        ->whenPrompted('Payment failed, but money was taken from my card.')
        ->assertHasKey('category')
        ->assertHasKey('priority')
        ->assertHasKey('sentiment')
        ->assertHasKey('summary')
        ->assertHasKey('needs_human_review');
});
```

## Test: billing issue classification

```php
it('classifies failed charged payments as billing high priority', function () {
    evaluate(TicketTriageAgent::class)
        ->whenPrompted('Payment failed, but money was taken from my card.')
        ->assertJsonPath('category', 'billing')
        ->assertJsonPath('priority', 'high')
        ->assertJsonPath('sentiment', 'frustrated')
        ->assertJsonPath('needs_human_review', true);
});
```

## Test: feature request classification

```php
it('classifies product suggestions as feature requests', function () {
    evaluate(TicketTriageAgent::class)
        ->whenPrompted('Can you add dark mode to the dashboard?')
        ->assertJsonPath('category', 'feature_request')
        ->assertJsonPath('priority', 'low')
        ->assertJsonPath('needs_human_review', false);
});
```

## Speaker line

> This is not about judging writing quality.  
> This is a contract test.  
> If our application expects these fields, the agent must return them.

---

# Test type 3: Criteria checks with custom rubrics

## Goal

Show that evals can judge human-facing text where there is no exact expected output.

This section belongs to `SupportReplyAgent`.

The reply agent receives only the customer message.

## Test: suggested reply quality with simple criteria

```php
use App\Ai\Agents\SupportReplyAgent;

it('generates a helpful reply for a billing issue', function () {
    evaluate(SupportReplyAgent::class)
        ->whenPrompted('Payment failed, but money was taken from my card.')
        ->toMeet('The reply is polite and empathetic')
        ->toMeet('The reply clearly asks for useful payment details')
        ->toMeet('The reply does not promise an immediate refund')
        ->toMeet('The reply says the issue will be reviewed');
});
```

## Test: angry customer reply

```php
it('does not blame an angry customer', function () {
    evaluate(SupportReplyAgent::class)
        ->whenPrompted('Your app charged me twice. This is unacceptable. I need this fixed today.')
        ->toMeet('The reply stays calm and professional')
        ->toMeet('The reply acknowledges the customer’s frustration')
        ->toMeet('The reply does not blame the customer')
        ->toMeet('The reply does not make guarantees the company cannot control')
        ->assertNotContains('your fault')
        ->assertNotContains('you should have');
});
```

## Custom rubric

```php
namespace App\Evals\Rubrics;

use Redberry\Evals\Contracts\Rubric;

final class SupportReplyQuality extends Rubric
{
    public function description(): string
    {
        return <<<'PROMPT'
        Evaluate whether this is a high-quality customer support reply.

        A good reply should:
        - Be polite and empathetic.
        - Acknowledge the customer's issue.
        - Be clear and easy to understand.
        - Ask for the next required information when needed.
        - Avoid blaming the customer.
        - Avoid making promises the company cannot guarantee.
        - Be concise enough for a support message.
        PROMPT;
    }

    public function scored(): bool
    {
        return true;
    }
}
```

## Test: suggested reply passes reusable rubric

```php
use App\Ai\Agents\SupportReplyAgent;
use App\Evals\Rubrics\SupportReplyQuality;

it('passes the support reply quality rubric', function () {
    evaluate(SupportReplyAgent::class)
        ->whenPrompted('Payment failed, but money was taken from my card.')
        ->toMeet(new SupportReplyQuality, 85);
});
```

## Speaker line

> For human-facing AI output, exact matching is usually the wrong tool.  
> We need to evaluate quality, and rubrics help us make that quality standard reusable.

---

# Suggested demo order

Use this order in the talk:

## 1. Introduce the two agents

```text
TicketTriageAgent
→ customer message in
→ structured ticket data out

SupportReplyAgent
→ customer message in
→ suggested reply out
```

## 2. Show structured output validation

```php
->assertHasKey('category')
->assertHasKey('priority')
->assertHasKey('needs_human_review')
```

## 3. Show business rule validation

```php
->assertJsonPath('category', 'billing')
->assertJsonPath('priority', 'high')
```

## 4. Show tool use check

```php
->assertToolUsed(CustomerLookupTool::class)
```

## 5. Show suggested reply criteria

```php
->toMeet('The reply is polite and empathetic')
->toMeet('The reply does not promise an immediate refund')
```

## 6. Show custom rubric

```php
->toMeet(new SupportReplyQuality, 85)
```

---

# Best demo tickets

Use only a few examples. Four are enough.

## Ticket A — billing, high priority

```text
Payment failed, but money was taken from my card.
```

Expected triage:

```json
{
  "category": "billing",
  "priority": "high",
  "sentiment": "frustrated",
  "needs_human_review": true
}
```

Expected reply quality:

```text
- apologetic
- empathetic
- asks for payment details
- does not promise instant refund
```

## Ticket B — technical, high priority

```text
I cannot log in. I get a 500 error after entering my password.
```

Expected triage:

```json
{
  "category": "technical",
  "priority": "high",
  "sentiment": "frustrated",
  "needs_human_review": true
}
```

Expected reply quality:

```text
- clear
- calm
- asks for browser/device or screenshot if needed
- says the team will investigate
```

## Ticket C — feature request, low priority

```text
Can you add dark mode to the dashboard?
```

Expected triage:

```json
{
  "category": "feature_request",
  "priority": "low",
  "sentiment": "neutral",
  "needs_human_review": false
}
```

Expected reply quality:

```text
- thanks the customer
- says the suggestion will be shared or considered
- does not promise a release date
```

## Ticket D — angry billing, urgent

```text
Your app charged me twice. This is unacceptable. I need this fixed today.
```

Expected triage:

```json
{
  "category": "billing",
  "priority": "urgent",
  "sentiment": "angry",
  "needs_human_review": true
}
```

Expected reply quality:

```text
- stays calm
- acknowledges the issue
- asks for transaction details
- does not blame the customer
- does not make unsafe promises
```

---

Final speaker line:

> Each agent is evaluated according to its job.  
> The triage agent is tested like a decision system.  
> The reply agent is tested like a communication system.  
> That is what makes evals practical.

