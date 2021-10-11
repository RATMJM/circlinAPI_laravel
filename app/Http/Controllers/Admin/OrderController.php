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
        $filter = $request->get('filter', 'all');
        $type = $request->get('type', 'all');
        $keyword = $request->get('keyword');

        $date = [
            'all' => Order::withoutTrashed(),
            'day' => Order::where('orders.created_at', '>=', date('Y-m-d')),
            'week' => Order::where('orders.created_at', '>=', date('Y-m-d', time() - (86400 * date('w')))),
            'month' => Order::where('orders.created_at', '>=', date('Y-m')),
        ];
        $orders_count = [];
        foreach ($date as $i => $item) {
            $orders_count[$i] = $item->count();
        }

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
            ->select([
                'orders.id', 'orders.created_at', 'orders.order_no', 'orders.total_price', 'orders.use_point',
                'users.nickname', 'users.email',
                'order_destinations.post_code', 'order_destinations.address','order_destinations.address_detail',
                'order_destinations.recipient_name', 'order_destinations.phone', 'order_destinations.comment',
            ])
            ->with('order_products', function ($query) {
                $query->where(function ($query) {
                    $query->whereNotNull('order_products.product_id')
                        ->orWhereNotNull('order_products.brand_id');
                })
                    ->leftJoin('products', 'products.id', 'order_products.product_id')
                    ->leftJoin('brands', 'brands.id', 'products.brand_id')
                    ->leftJoin('brands as ship_brands', 'ship_brands.id', 'order_products.brand_id')
                    ->leftJoin('order_product_options', 'order_product_options.order_product_id', 'order_products.id')
                    ->leftJoin('product_options', 'product_options.id', 'order_product_options.product_option_id')
                    ->leftJoin('order_product_deliveries', 'order_product_deliveries.order_product_id', 'order_products.id')
                    ->select([
                        'order_products.order_id', 'order_products.id',
                        'products.id as product_id', 'products.name_ko as product_name', 'order_products.price as product_price',
                        'product_options.name_ko as option_name', 'order_products.qty',
                        'brands.id as brand_id', 'brands.name_ko as brand_name',
                        'ship_brands.id as ship_brand_id', 'ship_brands.name_ko as ship_brand_name',
                        'order_product_deliveries.company', 'order_product_deliveries.tracking_no',
                        'order_product_deliveries.qty as delivery_qty', 'order_product_deliveries.completed_at',
                    ])
                    ->orderBy('order_products.id')
                    ->orderBy('product_options.group');
            })
            ->orderBy('orders.id', 'desc')
            ->paginate(50);

        return view('admin.order', [
            'orders_count' => $orders_count,
            'orders' => $orders,
            'type' => $type,
            'keyword' => $keyword,
        ]);
    }
}
