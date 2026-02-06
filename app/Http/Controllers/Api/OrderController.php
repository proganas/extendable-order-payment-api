<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function index(Request $request)
    {
        $all_orders = $this->order->where('user_id', auth()->id());

        if ($request->filled('status')) {
            $allowedStatuses = ['pending', 'confirmed', 'cancelled'];

            if (!in_array($request->status, $allowedStatuses)) {
                return response()->json([
                    'message' => 'Invalid order status filter'
                ], 422);
            }

            $all_orders->where('status', $request->status);
        }

        $orders = $all_orders->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function store(CreateOrderRequest $request)
    {
        $data = $request->only('name', 'price', 'quantity');
        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';
        $data['total_amount'] = $data['price'] * $data['quantity'];

        $order = $this->order->create($data);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $order = $this->order->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be updated'], 400);
        }

        $data = $request->only('name', 'price', 'quantity');

        $price = $data['price'] ?? $order->price;
        $quantity = $data['quantity'] ?? $order->quantity;

        $data['total_amount'] = $price * $quantity;
        $order->update($data);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order,
        ]);
    }

    public function cancel($id)
    {
        $order = $this->order->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $order,
        ]);
    }

    public function confirm($id)
    {
        $order = $this->order->where('id', $id)->where('user_id', auth()->id())->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be confirmed'], 400);
        }

        $order->status = 'confirmed';
        $order->save();

        return response()->json([
            'message' => 'Order confirmed successfully',
            'order' => $order,
        ]);
    }

    public function destroy($id)
    {
        $order = $this->order
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be deleted'
            ], 400);
        }

        if ($order->payments()->exists()) {
            return response()->json([
                'message' => 'Order cannot be deleted because it has payments'
            ], 400);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }

}
