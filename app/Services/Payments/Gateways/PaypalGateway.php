<?php

namespace App\Services\Payments\Gateways;

class PaypalGateway implements PaymentGatewayInterface
{
    public function process(float $amount): array
    {
        return [
            'status' => 'successful',
            'transaction_id' => uniqid('paypal_'),
        ];
    }
}
