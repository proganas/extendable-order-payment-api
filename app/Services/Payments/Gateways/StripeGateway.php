<?php

namespace App\Services\Payments\Gateways;

class StripeGateway implements PaymentGatewayInterface
{
    public function process(float $amount): array
    {
        return [
            'status' => 'successful',
            'transaction_id' => uniqid('stripe_'),
        ];
    }
}
