<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportReplyAgent;
use App\Ai\Agents\TicketTriageAgent;
use App\Ai\Support\DemoSupportTickets;
use App\Ai\Tools\CustomerLookupTool;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JsonException;
use Throwable;

class SupportDemoController extends Controller
{
    private const AI_PROMPT_TIMEOUT_SECONDS = 25;

    public function index(DemoSupportTickets $demoTickets): View
    {
        return view('support-demo.index', [
            'tickets' => $demoTickets->all(),
            'selectedTicketId' => null,
            'result' => null,
            'error' => null,
        ]);
    }

    public function triage(Request $request, DemoSupportTickets $demoTickets): View
    {
        $validated = $request->validate([
            'ticket_id' => ['required', 'string', Rule::in($demoTickets->ids())],
        ]);

        $ticket = $demoTickets->find($validated['ticket_id']);

        abort_if($ticket === null, 404);

        $this->extendExecutionTime();

        try {
            $triage = TicketTriageAgent::make(
                customerIdentifier: $ticket['customer_identifier'],
            )->prompt($ticket['message'], timeout: self::AI_PROMPT_TIMEOUT_SECONDS);

            $reply = SupportReplyAgent::make()
                ->prompt($ticket['message'], timeout: self::AI_PROMPT_TIMEOUT_SECONDS);
        } catch (Throwable $exception) {
            report($exception);

            return view('support-demo.index', [
                'tickets' => $demoTickets->all(),
                'selectedTicketId' => $ticket['id'],
                'result' => null,
                'error' => [
                    'ticket' => $ticket,
                    'message' => 'The AI provider did not return a response before the demo timeout. Please try again.',
                    'detail' => config('app.debug') ? class_basename($exception).': '.$exception->getMessage() : null,
                ],
            ]);
        }

        $toolCalls = $this->normalizeToolData($triage->toolCalls);
        $toolResults = $this->normalizeToolResults($triage->toolResults);

        return view('support-demo.index', [
            'tickets' => $demoTickets->all(),
            'selectedTicketId' => $ticket['id'],
            'error' => null,
            'result' => [
                'ticket' => $ticket,
                'triage' => $triage->toArray(),
                'reply' => $this->formatSuggestedReply($reply->text),
                'customer_lookup_was_used' => $this->customerLookupWasUsed($toolCalls, $toolResults),
                'tool_calls' => $toolCalls,
                'tool_results' => $toolResults,
                'usage' => [
                    'triage' => $triage->usage->toArray(),
                    'reply' => $reply->usage->toArray(),
                ],
                'meta' => [
                    'triage' => $triage->meta->toArray(),
                    'reply' => $reply->meta->toArray(),
                ],
            ],
        ]);
    }

    private function extendExecutionTime(): void
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(90);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeToolData(iterable $items): array
    {
        return collect($items)
            ->map(fn (mixed $item): array => method_exists($item, 'toArray') ? $item->toArray() : (array) $item)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeToolResults(iterable $items): array
    {
        return collect($this->normalizeToolData($items))
            ->map(function (array $toolResult): array {
                $toolResult['decoded_result'] = $this->decodeToolResult($toolResult['result'] ?? null);

                return $toolResult;
            })
            ->all();
    }

    private function customerLookupWasUsed(array $toolCalls, array $toolResults): bool
    {
        $customerLookupTool = class_basename(CustomerLookupTool::class);

        return collect([...$toolCalls, ...$toolResults])
            ->contains(fn (array $toolEvent): bool => ($toolEvent['name'] ?? null) === $customerLookupTool);
    }

    private function decodeToolResult(mixed $result): mixed
    {
        if (! is_string($result)) {
            return $result;
        }

        try {
            return json_decode($result, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $result;
        }
    }

    private function formatSuggestedReply(string $reply): string
    {
        $reply = preg_replace('/(?<!\n)\s+-\s+/', "\n- ", $reply) ?? $reply;
        $reply = preg_replace("/\n{3,}/", "\n\n", $reply) ?? $reply;

        return trim($reply);
    }
}
