<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Gateways\PaymentGatewayInterface;

class PaymentService
{
    public function process(Order $order, PaymentGatewayInterface $gateway, int $paymentGatewayId)
    {
        if ($order->status !== 'confirmed') {
            throw new \Exception('Payment allowed only for confirmed orders');
        }

        $result = $gateway->process($order->total_amount);

        return Payment::create([
            'order_id' => $order->id,
            'payment_gateway_id' => $paymentGatewayId,
            'status' => $result['status'],
            'amount' => $order->total_amount,
            'transaction_id' => $result['transaction_id'] ?? null,
        ]);
    }
}
