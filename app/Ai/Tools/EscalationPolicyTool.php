<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class EscalationPolicyTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Select the escalation path for a support ticket category and priority.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $ticketCategory = (string) ($request['ticket_category'] ?? 'other');
        $priority = (string) ($request['priority'] ?? 'medium');

        $policy = match ($ticketCategory) {
            'billing' => [
                'route_to' => 'billing_escalations',
                'requires_supervisor_review' => in_array($priority, ['high', 'urgent'], true),
                'sla_hours' => $priority === 'urgent' ? 2 : 4,
            ],
            'account' => [
                'route_to' => 'account_access_queue',
                'requires_supervisor_review' => $priority === 'urgent',
                'sla_hours' => $priority === 'urgent' ? 2 : 6,
            ],
            'feature_request' => [
                'route_to' => 'product_feedback',
                'requires_supervisor_review' => false,
                'sla_hours' => 72,
            ],
            default => [
                'route_to' => 'general_support',
                'requires_supervisor_review' => false,
                'sla_hours' => 24,
            ],
        };

        return collect([
            'ticket_category' => $ticketCategory,
            'priority' => $priority,
            'route_to' => $policy['route_to'],
            'requires_supervisor_review' => $policy['requires_supervisor_review'],
            'sla_hours' => $policy['sla_hours'],
            'reason' => $policy['requires_supervisor_review'] ? 'escalate_to_supervisor' : 'standard_queue',
        ])->toJson(JSON_THROW_ON_ERROR);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_category' => $schema->string()->enum(['billing', 'account', 'feature_request', 'other'])->description('The support category being evaluated.')->required(),
            'priority' => $schema->string()->enum(['low', 'medium', 'high', 'urgent'])->description('The urgency of the ticket.')->required(),
        ];
    }
}
