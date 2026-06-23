<?php

namespace App\Ai\Tools;

use App\Ai\Support\CustomerDirectory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class BillingHistoryTool implements Tool
{
    public function __construct(private ?CustomerDirectory $directory = null)
    {
        $this->directory = $directory ?? new CustomerDirectory;
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Review demo billing history by email or name for a support triage workflow.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $identifier = trim((string) ($request['customer_identifier'] ?? ''));
        $lookupBy = (string) ($request['lookup_by'] ?? 'auto');

        if ($identifier === '') {
            $customer = $this->directory->random();
            $sourceReason = 'fallback_random';
        } else {
            $customer = match (true) {
                $lookupBy === 'email' || filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false => $this->directory->findByEmail($identifier),
                $lookupBy === 'name', $lookupBy === 'auto' => $this->directory->findByName($identifier),
                default => null,
            };

            $sourceReason = 'explicit_identifier';
        }

        if ($customer === null) {
            return collect([
                'matched' => false,
                'customer_identifier' => $identifier,
                'lookup_by' => $lookupBy,
                'customer_id' => null,
                'name' => null,
                'email' => null,
                'billing_status' => 'unknown',
                'open_invoices' => 0,
                'last_payment_status' => null,
                'recommended_action' => 'ask_for_more_customer_details',
                'source_reason' => $sourceReason,
            ])->toJson(JSON_THROW_ON_ERROR);
        }

        $history = $this->billingHistoryFor($customer['customer_id']);

        return collect([
            'matched' => true,
            'customer_identifier' => $identifier !== '' ? $identifier : $customer['email'],
            'lookup_by' => $lookupBy,
            'customer_id' => $customer['customer_id'],
            'name' => $customer['name'],
            'email' => $customer['email'],
            'billing_status' => $history['billing_status'],
            'open_invoices' => $history['open_invoices'],
            'last_payment_status' => $customer['payment_status'],
            'recommended_action' => $history['recommended_action'],
            'source_reason' => $sourceReason,
        ])->toJson(JSON_THROW_ON_ERROR);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'customer_identifier' => $schema->string()->nullable()->description('Customer email address or name. May be null when no identifier is available.')->required(),
            'lookup_by' => $schema->string()->enum(['auto', 'email', 'name'])->description('How to interpret the customer identifier.')->required(),
        ];
    }

    /**
     * @return array{billing_status: string, open_invoices: int, recommended_action: string}
     */
    private function billingHistoryFor(string $customerId): array
    {
        return match ($customerId) {
            'cus_1001' => [
                'billing_status' => 'past_due',
                'open_invoices' => 2,
                'recommended_action' => 'request_invoice_details',
            ],
            'cus_1002' => [
                'billing_status' => 'paid',
                'open_invoices' => 0,
                'recommended_action' => 'send_standard_reply',
            ],
            'cus_1003' => [
                'billing_status' => 'none',
                'open_invoices' => 0,
                'recommended_action' => 'ask_for_payment_method',
            ],
            'cus_1004' => [
                'billing_status' => 'restricted',
                'open_invoices' => 1,
                'recommended_action' => 'escalate_to_billing_specialist',
            ],
            default => [
                'billing_status' => 'unknown',
                'open_invoices' => 0,
                'recommended_action' => 'ask_for_more_customer_details',
            ],
        };
    }
}
