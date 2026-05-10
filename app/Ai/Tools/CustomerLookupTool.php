<?php

namespace App\Ai\Tools;

use App\Ai\Support\CustomerDirectory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CustomerLookupTool implements Tool
{
    private CustomerDirectory $directory;

    public function __construct(?CustomerDirectory $directory = null)
    {
        $this->directory = $directory ?? new CustomerDirectory;
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up demo customer account context by email or name. If no identifier is provided, return a random demo customer.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $identifier = trim((string) ($request['customer_identifier'] ?? ''));
        $lookupBy = (string) ($request['lookup_by'] ?? 'auto');

        if ($identifier === '') {
            return $this->lookupResult($this->directory->random(), true, 'fallback_random');
        }

        $customer = match (true) {
            $lookupBy === 'email' || filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false => $this->directory->findByEmail($identifier),
            $lookupBy === 'name', $lookupBy === 'auto' => $this->directory->findByName($identifier),
            default => null,
        };

        return $this->lookupResult($customer, $customer !== null, 'explicit_identifier');
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
     * @param  array{customer_id: string, name: string, email: string, plan: string, payment_status: string, previous_tickets_count: int, account_state: string}|null  $customer
     */
    private function lookupResult(?array $customer, bool $matched, string $sourceReason): string
    {
        return collect([
            'matched' => $matched,
            'customer_id' => null,
            'name' => null,
            'email' => null,
            'plan' => null,
            'payment_status' => null,
            'previous_tickets_count' => null,
            'account_state' => null,
            'source_reason' => $sourceReason,
        ])
            ->merge(collect($customer)->only([
                'customer_id',
                'name',
                'email',
                'plan',
                'payment_status',
                'previous_tickets_count',
                'account_state',
            ]))
            ->toJson(JSON_THROW_ON_ERROR);
    }
}
