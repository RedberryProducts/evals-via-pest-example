<?php

namespace App\Ai\Support;

class DemoSupportTickets
{
    /**
     * @return array<int, array{id: string, label: string, customer_identifier: string, message: string}>
     */
    public function all(): array
    {
        return [
            [
                'id' => 'billing-failed-charge',
                'label' => 'Billing charge',
                'customer_identifier' => 'john@example.com',
                'message' => 'Payment failed, but money was taken from my card.',
            ],
            [
                'id' => 'technical-login-error',
                'label' => 'Login error',
                'customer_identifier' => 'amina@example.com',
                'message' => 'I cannot log in. I get a 500 error after entering my password.',
            ],
            [
                'id' => 'feature-dark-mode',
                'label' => 'Feature request',
                'customer_identifier' => 'miguel@example.com',
                'message' => 'Can you add dark mode to the dashboard?',
            ],
            [
                'id' => 'angry-double-charge',
                'label' => 'Double charge',
                'customer_identifier' => 'nora@example.com',
                'message' => 'Your app charged me twice. This is unacceptable. I need this fixed today.',
            ],
        ];
    }

    /**
     * @return array{id: string, label: string, customer_identifier: string, message: string}|null
     */
    public function find(string $id): ?array
    {
        return collect($this->all())->first(fn (array $ticket): bool => $ticket['id'] === $id);
    }

    /**
     * @return array<int, string>
     */
    public function ids(): array
    {
        return collect($this->all())->pluck('id')->all();
    }
}
