<?php

namespace App\Services\Payments\Gateways;

use App\Models\PaymentGateway;

class PaymentGatewayFactory
{
    public static function make(PaymentGateway $gateway): PaymentGatewayInterface
    {
        return match ($gateway->code) {
            'paypal' => new PaypalGateway(),
            'stripe' => new StripeGateway(),
            default => throw new \Exception('Unsupported payment gateway'),
        };
    }
}
