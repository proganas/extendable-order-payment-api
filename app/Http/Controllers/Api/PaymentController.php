<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Models\PaymentGateway;
use App\Services\Payments\Gateways\PaymentGatewayFactory;

class PaymentController extends Controller
{
    public function process(ProcessPaymentRequest $request, Order $order)
    {
        $gatewayModel = PaymentGateway::findOrFail($request->payment_gateway_id);

        if (!$gatewayModel->is_active) {
            return response()->json([
                'message' => 'Payment gateway is not active'
            ], 400);
        }

        $gateway = PaymentGatewayFactory::make($gatewayModel);

        $payment = app(PaymentService::class)
            ->process($order, $gateway, $gatewayModel->id);

        return response()->json([
            'message' => 'Payment processed',
            'payment' => $payment,
        ]);
    }

    public function index()
    {
        $payments = Payment::whereHas('order', function ($q) {
            $q->where('user_id', auth()->id());
        })->with('order')->get();

        return response()->json([
            'payments' => $payments
        ]);
    }

    public function order_payments(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'payments' => $order->payments
        ]);
    }
}
