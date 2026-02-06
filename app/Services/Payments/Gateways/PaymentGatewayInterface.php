<?php

namespace App\Services\Payments\Gateways;

interface PaymentGatewayInterface
{
    public function process(float $amount): array;
}
