<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(): array
    {
        $data = Product::where('products.is_show', true)
            ->join('brands', 'brands.id', 'products.brand_id')
            ->select([
                'products.id', 'products.code', 'brands.name_ko as brand_name', 'products.name_ko as product_name',
                'products.thumbnail_image',
                'products.shipping_fee', 'products.price', 'products.sale_price', 'products.status',
                DB::raw("ROUND(100-(products.sale_price/products.price*100)) as discount_rate"),
            ])
            ->orderBy(DB::raw("products.status='sale'"), 'desc')
            ->orderBy('products.order', 'desc')
            ->orderBy('products.id', 'desc')
            ->get();

        return $data;
    }

    public function create(): array
    {
        //
    }

    public function store(Request $request): array
    {
        //
    }

    public function show($id): array
    {
        //
    }

    public function edit($id): array
    {
        //
    }

    public function update(Request $request, $id): array
    {
        //
    }

    public function destroy($id): array
    {
        //
    }
}
