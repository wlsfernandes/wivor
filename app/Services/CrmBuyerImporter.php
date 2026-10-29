<?php

namespace App\Services;

use App\Models\CrmContact;
use App\Models\Customer;
use App\Models\Order;

/**
 * Add photo buyers and customer accounts to the CRM directory without recording any consent.
 */
class CrmBuyerImporter
{
    private const CHUNK_SIZE = 500;

    /** @return array{customerAccounts: int, orderEmails: int} number of addresses examined per source */
    public function import(): array
    {
        return [
            'customerAccounts' => $this->importCustomerAccounts(),
            'orderEmails' => $this->importPaidOrderEmails(),
        ];
    }

    private function importCustomerAccounts(): int
    {
        $examined = 0;

        Customer::query()
            ->join('users', 'users.id', '=', 'customers.user_id')
            ->select(['customers.id', 'users.email', 'customers.first_name', 'customers.last_name', 'customers.phone'])
            ->orderBy('customers.id')
            ->chunk(self::CHUNK_SIZE, function ($customers) use (&$examined): void {
                $examined += $customers->count();
                $this->upsert($customers->map(fn (Customer $customer): array => [
                    'email' => $customer->email,
                    'first_name' => $customer->first_name,
                    'last_name' => $customer->last_name,
                    'phone' => $customer->phone,
                ])->all(), CrmContact::SOURCE_CUSTOMER_ACCOUNT);
            });

        return $examined;
    }

    private function importPaidOrderEmails(): int
    {
        $examined = 0;

        Order::query()
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereNotNull('customer_email')
            ->select(['id', 'customer_email'])
            ->orderBy('id')
            ->chunk(self::CHUNK_SIZE, function ($orders) use (&$examined): void {
                $examined += $orders->count();
                $this->upsert($orders->map(fn (Order $order): array => [
                    'email' => $order->customer_email,
                    'first_name' => null,
                    'last_name' => null,
                    'phone' => null,
                ])->all(), CrmContact::SOURCE_ORDER);
            });

        return $examined;
    }

    /** @param list<array{email: string|null, first_name: string|null, last_name: string|null, phone: string|null}> $people */
    private function upsert(array $people, string $source): void
    {
        $peopleByEmail = [];

        foreach ($people as $person) {
            $email = trim((string) $person['email']);

            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $peopleByEmail[CrmContact::normalizeEmail($email)] ??= array_merge($person, ['email' => $email]);
        }

        if ($peopleByEmail === []) {
            return;
        }

        $now = now();
        CrmContact::query()->insertOrIgnore(array_map(fn (string $normalizedEmail, array $person): array => [
            'email_normalized' => $normalizedEmail,
            'email_original' => $person['email'],
            'first_name' => $person['first_name'],
            'last_name' => $person['last_name'],
            'phone' => $person['phone'],
            'first_source' => $source,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_keys($peopleByEmail), $peopleByEmail));

        $this->fillEmptyNames($peopleByEmail);
    }

    /** @param array<string, array{first_name: string|null, last_name: string|null, phone: string|null}> $peopleByEmail */
    private function fillEmptyNames(array $peopleByEmail): void
    {
        CrmContact::query()
            ->whereIn('email_normalized', array_keys($peopleByEmail))
            ->get()
            ->each(function (CrmContact $contact) use ($peopleByEmail): void {
                foreach (['first_name', 'last_name', 'phone'] as $field) {
                    $contact->{$field} ??= $peopleByEmail[$contact->email_normalized][$field];
                }

                $contact->save();
            });
    }
}
