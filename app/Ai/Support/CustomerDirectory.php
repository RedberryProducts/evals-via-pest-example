<?php

namespace App\Ai\Support;

class CustomerDirectory
{
    /**
     * @return array<int, array{customer_id: string, name: string, email: string, plan: string, payment_status: string, previous_tickets_count: int, account_state: string}>
     */
    public function customers(): array
    {
        return [
            [
                'customer_id' => 'cus_1001',
                'name' => 'John Carter',
                'email' => 'john@example.com',
                'plan' => 'Pro',
                'payment_status' => 'past_due',
                'previous_tickets_count' => 4,
                'account_state' => 'active',
            ],
            [
                'customer_id' => 'cus_1002',
                'name' => 'Amina Patel',
                'email' => 'amina@example.com',
                'plan' => 'Team',
                'payment_status' => 'paid',
                'previous_tickets_count' => 1,
                'account_state' => 'active',
            ],
            [
                'customer_id' => 'cus_1003',
                'name' => 'Miguel Santos',
                'email' => 'miguel@example.com',
                'plan' => 'Free',
                'payment_status' => 'none',
                'previous_tickets_count' => 0,
                'account_state' => 'trial',
            ],
            [
                'customer_id' => 'cus_1004',
                'name' => 'Nora Williams',
                'email' => 'nora@example.com',
                'plan' => 'Enterprise',
                'payment_status' => 'paid',
                'previous_tickets_count' => 7,
                'account_state' => 'restricted',
            ],
        ];
    }

    /**
     * @return array{customer_id: string, name: string, email: string, plan: string, payment_status: string, previous_tickets_count: int, account_state: string}|null
     */
    public function findByEmail(string $email): ?array
    {
        $normalizedEmail = mb_strtolower(trim($email));

        return collect($this->customers())
            ->first(fn (array $customer): bool => mb_strtolower($customer['email']) === $normalizedEmail);
    }

    /**
     * @return array{customer_id: string, name: string, email: string, plan: string, payment_status: string, previous_tickets_count: int, account_state: string}|null
     */
    public function findByName(string $name): ?array
    {
        $normalizedName = mb_strtolower(trim($name));

        if ($normalizedName === '') {
            return null;
        }

        return collect($this->customers())
            ->first(fn (array $customer): bool => str_contains(mb_strtolower($customer['name']), $normalizedName));
    }

    /**
     * @return array{customer_id: string, name: string, email: string, plan: string, payment_status: string, previous_tickets_count: int, account_state: string}
     */
    public function random(): array
    {
        return collect($this->customers())->random();
    }
}
