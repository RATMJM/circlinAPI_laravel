<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $keyword = $request->get('keyword');

        $orders = Order::when($type, function ($query, $type) use ($keyword) {
            match ($type) {
                'all' => $query->where(function ($query) use ($keyword) {
                    $query->where('users.nickname', 'like', "%$keyword%")
                        ->orWhere('users.email', 'like', "%$keyword%");
                }),
                default => null,
            };
        })
            ->join('users', 'users.id', 'orders.user_id')
            ->join('order_destinations', 'order_destinations.order_id', 'orders.id')
            ->join('order_products', 'order_products.order_id', 'orders.id')
            ->join('products', 'products.id', 'order_products.product_id')
            ->leftJoin('order_product_options', 'order_product_options.order_product_id', 'order_products.id')
            ->leftJoin('product_options', 'product_options.id', 'order_product_options.product_option_id')
            ->leftJoin('order_product_deliveries', 'order_product_deliveries.order_product_id', 'order_products.id')
            ->select([
                'orders.id', 'orders.order_no', 'users.nickname', 'users.email',
                'products.id as product_id', 'products.name_ko as product_name', 'product_options.name_ko as option_name', 'order_products.qty',
                'order_destinations.post_code', 'order_destinations.address','order_destinations.address_detail',
                'order_destinations.recipient_name', 'order_destinations.phone', 'order_destinations.comment',
                'order_product_deliveries.company', 'order_product_deliveries.tracking_no',
                'order_product_deliveries.qty as delivery_qty', 'order_product_deliveries.completed_at',
            ])
            ->orderBy('orders.id', 'desc')
            ->orderBy('order_products.id')
            ->orderBy('order_product_options.id')
            ->paginate(50);

        return view('admin.order', [
            'orders' => $orders,
        ]);
    }
}
