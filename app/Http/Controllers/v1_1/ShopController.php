<?php

namespace App\Http\Controllers\v1_1;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartOption;
use App\Models\Order;
use App\Models\PointHistory;
use App\Models\Product;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    // 샵 아이템리스트 조회
    public function item_list(Request $request): array
    {
        //  $user_id = '4';//token()->uid;
        //  $category = '2';//$request->get('category'); // 카테고리
        //  $type = 'high';//$request->get('type'); // 필터값
        $user_id = token()->uid;
        $category = $request->get('category', '전체'); // 카테고리
        $type = $request->get('type'); // 필터값
        try {
            DB::beginTransaction();
            if ($category == '전체') {
                if ($type == "hot") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                order by a.status="sale" desc, a.`order` desc, a.id desc;', ['1']);

                } elseif ($type == "high") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                order by a.status="sale" desc, `order` desc, sale_price desc;', ['1']);
                } elseif ($type == "low") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                order by a.status="sale" desc, `order` desc, sale_price ;', ['1']);
                } else {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                order by a.status="sale" desc, `order` desc, a.id desc;', ['1']);
                }
            } else {// ㅋㅏ테고리 눌렀을떄
                if ($type == "hot") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and  a.product_category_id=?
                and b.id=a.brand_id
                order by a.status="sale" desc, a.`order` desc, a.id desc;', ['1', $category]);


                } elseif ($type == "high") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=?
                order by a.status="sale" desc, `order` desc, sale_price desc;', ['1', $category]);
                } elseif ($type == "low") {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=?
                order by a.status="sale" desc, `order` desc, sale_price ;', ['1', $category]);
                } else {

                    $itemList = DB::select('select a.id as product_id, case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b
                WHERE  deleted_at is null and
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=?
                order by a.status="sale" desc, `order` desc, a.id desc;', ['1', $category]);
                }
            }

            return success([
                'result' => true,
                'itemList' => $itemList,
                'user_id' => $user_id,
                'category' => $category,
                'type' => $type,
            ]);

            // else {
            //     DB::rollBack();
            //     return success([
            //         'result' => false,
            //         'reason' => 'not enough data',
            //     ]);
            // }
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }


        return success([
            'result' => true,
            'areas' => $areas,
        ]);
    }

    // 샵 카테고리 조회
    public function shop_category(Request $request): array
    {
        $data = ProductCategory::select(['id as product_category_id', 'title'])
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->orderBy('id')
            ->get();

        return success([
            'result' => true,
            'categoryList' => $data,
        ]);
    }

    //샵 배너 조회
    public function shop_banner(): array
    {

        try {
            DB::beginTransaction();

            // $shopBannerList = (new BannerController())->index('shop');

            $shopBannerList = DB::select('select b.id, b.image, b.product_id, b.link_url, \'' . date('Y-m-d H:i:s') . '\',b.started_at,b.ended_at
                From products a , banners b
                where b.type=\'shop\' and
                \'' . date('Y-m-d H:i:s') . '\' >= b.started_at and (b.ended_at is null or b.ended_at > \'' . date('Y-m-d H:i:s).') . '\')
                and b.product_id=a.id and b.deleted_at is null
                order by sort_num desc, b.id desc;');

            return success([
                'result' => true,
                'shopBannerList' => $shopBannerList,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    // 포인트 내역 조회
    public function shop_point_list(Request $request): array
    {
        $user_id = token()->uid;

        try {
            DB::beginTransaction();

            $pointInfo = DB::select('SELECT
                (
                select ifnull(sum(POINT),0)
                from  point_histories
                where user_id=? and point>0
                and substr(created_at,1,10) BETWEEN  substr(date_add(sysdate(), interval -365 day ),1,10)  and  substr(date_add(sysdate(), interval 9 hour),1,10)
                )  as YEAR_POINT
                ,
                (
                SELECT ifnull(sum(POINT),0) as Y_POINT
                from point_histories
                where user_id=? and point>0
                and substr(created_at,1,10) BETWEEN  substr(date_add(sysdate(), interval -7 day ),1,10)  and  substr(date_add(sysdate(), interval 9 hour),1,10)
                )  as WEEK_POINT
                FROM
                DUAL;', [$user_id, $user_id]);

            $shopPointList = PointHistory::where('point_histories.user_id', $user_id)
                ->leftJoin('common_codes', function ($query) {
                    $query->on('common_codes.ctg_sm', 'point_histories.reason')
                        ->where('ctg_lg', 'point_histories');
                })
                ->leftJoin('missions', function($query) {
                    $query->on('missions.id', 'point_histories.mission_id');
                })
                ->select([
                    'point_histories.created_at',
                    'point_histories.point',
                    'point_histories.user_id',
                    // 'point_histories.reason',
                    // DB::raw('IF(point_histories.mission_id IS NULL, point_histories.reason, CONCAT(point_histories.reason, "|", missions.title)) AS reason'),
                    DB::raw('IF(point_histories.mission_id IS NOT NULL AND missions.mission_type = "commercial_food", CONCAT(point_histories.reason, "|", missions.title), point_histories.reason) AS reason'),
                    // DB::raw('IFNULL(point_histories.reason, CONCAT(point_histories.reason, "|", missions.title)) AS mission_title'),
                    'common_codes.content_ko as message',
                ])
                ->orderBy('point_histories.id', 'desc')
                ->get();

            foreach ($shopPointList as $i => $item) {
                $replaces = [
                    // 'nickname' => $item->latest_nickname,
                ];

                $item->message = code_replace($item->message, $replaces);
            }

            return success([
                'result' => true,
                'pointInfo' => $pointInfo,
                'shopPointList' => $shopPointList,

            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    //구매내역 리스트 조회
    public function bought_product_list(Request $request): array
    {
        $user_id = token()->uid;

        try {
            DB::beginTransaction();

            $orderList = DB::select('select a.id as order_id, f.id as product_id,
                ORDER_NO, a.total_price, f.name_ko as product_name, g.name_ko as brand_name, f.thumbnail_image, f.code, a.created_at as order_time,
                h.id as feed_product_id,  "" SELECT_YN, b.qty, e.company, e.tracking_no,
                case when e.tracking_no is null then "상품준비중" else case when e.completed_at is null then "배송중" else "배송완료" end end as status ,
                concat(
                (opt1.name_ko  ) , " / ",
                (opt2.name_ko  ) , " / ",
                (opt3.name_ko  ) , " / ",
                (opt4.name_ko  ) , " / ",
                (opt5.name_ko  ) , " / ",
                (opt6.name_ko  ) , " / "
                 )  as option_name
                 from
                orders a,
                order_products b
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 0,1 ) opt1 ON opt1.order_product_id=b.id
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 1,1 ) opt2 ON opt2.order_product_id=b.id
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 2,1 ) opt3 ON opt3.order_product_id=b.id
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 3,1 ) opt4 ON opt4.order_product_id=b.id
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 4,1 ) opt5 ON opt5.order_product_id=b.id
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 5,1 ) opt6 ON opt6.order_product_id=b.id
                LEFT JOIN order_product_deliveries e on  b.id=e.order_product_id,
                order_destinations d,

                products f LEFT JOIN feed_products h ON f.id=h.product_id,
                brands g
                where
                a.id=b.order_id
                and a.id=d.order_id
                and f.id=b.product_id

                and f.brand_id = g.id
                and a.user_id=?
                and a.deleted_at is null
                order by a.id desc , product_id desc
                ;', [$user_id]);


            //         $buyPointInfo = DB::select('SELECT  ifnull((SELECT sum(POINT) FROM CAREPOINT where user_pk='$uid'
            //         and SERVICE_CODE='BUYINFO' and CP_STATE='1'),'0') as TOT_POINT,
            //         (SELECT COUNT(PRODCODE) *10
            //         FROM  ORDER_DET b LEFT JOIN ITEM_REVIEW a on b.ORD_DET_NO=a.ORD_DET_NO and a.USER_PK=b.USER_PK
            //          , PRODUCT_MASTER c, MEMBERDATA d
            //          WHERE b.USER_PK='$uid'
            //          and b.ITEM_CODE=c.PRODCODE
            //          and b.USER_PK=d._ID
            //          and a.ORD_DET_NO is null
            //          ORDER BY ORD_TIME DESC) as NO_REV_POINT
            //    FROM DUAL ;', array($user_id)  ) ;
            $buyPointInfo = '';

            return success([
                'result' => true,
                'orderList' => $orderList,
                'buyPointInfo' => $buyPointInfo,

            ]);


        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    //장바구니 조회
    public function cart_list(Request $request): array
    {
        $user_id = token()->uid;

        try {
            DB::beginTransaction();

            $cartList = Cart::where('carts.user_id', $user_id)
                ->join('products', 'products.id', 'carts.product_id')
                ->join('brands', 'brands.id', 'products.brand_id')
                ->select([
                    'carts.id as cart_id',
                    'products.thumbnail_image',
                    'brands.name_ko as brand_name',
                    'brands.user_id',
                    'carts.qty',
                    'products.name_ko as product_name',
                    'products.price as original_price',
                    'products.sale_price',
                    'carts.product_id',
                    'products.status',
                    'products.shipping_fee',
                    'products.brand_id',
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 0,1),'') as opt_name1"),
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 1,1),'') as opt_name2"),
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 2,1),'') as opt_name3"),
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 3,1),'') as opt_name4"),
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 4,1),'') as opt_name5"),
                    DB::raw("ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 5,1),'') as opt_name6"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 0,1),'') as opt1"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 1,1),'') as opt2"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 2,1),'') as opt3"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 3,1),'') as opt4"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 4,1),'') as opt5"),
                    DB::raw("ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 5,1),'') as opt6"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 0,1),0) as price1"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 1,1),0) as price2"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 2,1),0) as price3"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 3,1),0) as price4"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 4,1),0) as price5"),
                    DB::raw("ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and carts.id=y.cart_id limit 5,1),0) as price"),
                ])
                ->orderBy('carts.id', 'desc')
                ->get();

            /*$cartList = DB::select('
        select a.id as cart_id, c.thumbnail_image    , e.nickname as brand_name, e.id as user_id, a.qty , c.name_ko as product_name, sale_price,
                    c.id as product_id , c.status, c.shipping_fee,  c.brand_id,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1),"") as opt_name1,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1),"") as opt_name2,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1),"") as opt_name3,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1),"") as opt_name4,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1),"") as opt_name5,
                ifnull((select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1),"") as opt_name6,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1),"") as opt1,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1),"") as opt2,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1),"") as opt3,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1),"") as opt4,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1),"") as opt5,
                ifnull((select x.id from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1),"") as opt6,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1),0) as price1,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1),0) as price2,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1),0) as price3,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1),0) as price4,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1),0) as price5,
                ifnull((select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1),0) as price6



               from carts a,
               products c,
               brands d,
               users e

               where a.user_id=?
               and a.product_id=c.id
               and d.user_id=e.id
               and c.brand_id=d.id; ', [$user_id]);*/


            return success([
                'result' => true,
                'cartList' => $cartList,
            ]);


        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    // 장바구니 입력
    public function cart(Request $request): array
    {
        $user_id = token()->uid;
        $product_id = $request->get('product_id');
        $qty = $request->get('qty');
        $options = $request->get('options'); //option_Id, price
        $time = date("Y-m-d H:i:s");

        // $options =
        //       array([
        //         "option_id" => 281,
        //         'price'=> 14
        //       ],
        //       [
        //         "option_id" => 280,
        //         'price'=> 24
        //       ],
        //       [
        //         "option_id" => '',
        //         'price'=> 34
        //       ]);

        try {
            DB::beginTransaction();

            $tmp = [];
            foreach ($options as $option) {
                if ($option['option_id']) {
                    $tmp[] = $option;
                }
            }
            $options = $tmp;

            $cart = Cart::where(['user_id' => $user_id, 'product_id' => $product_id])
                ->where(CartOption::selectRaw("COUNT(1)")->whereColumn('cart_id', 'carts.id'), count($options))
                ->where(function ($query) use ($options) {
                    foreach ($options as $option) {
                        $query->whereHas('cart_options', function ($query) use ($option) {
                            $query->where('product_option_id', $option['option_id']);
                        });
                    }
                })
                ->first();

            if ($cart) {
                $cart = $cart->increment('qty', $qty);
            } else {
                $cart = Cart::create([
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'qty' => $qty,
                ]);

                //옵션입력
                $option = [];
                foreach ($options as $key => $value) {
                    $option[] = ['product_option_id' => $value['option_id'], 'price' => $value['price']];
                }
                $option = $cart->cart_options()->createMany($option);
            }

            DB::commit();

            return success(['result' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
    }

    // //상품주문
    // public function order_product(Request $request): array
    // {
    //         $user_id = 1;//; token()->uid;

    //         // $product_id = $request->get('product_id');
    //         $post_code = '111';//$request->get('post_code');
    //         $address = '주소';//$request->get('address');
    //         $address_detail = '상세';//$request->get('address_detail'); //상세주소
    //         $recipient_name = '박태정';// $request->get('recipient_name');  // 받는사람 이름
    //         $totalPrice = '9999';//$request->get('totalPrice'); //구매총액
    //         $used_point = '111';//$request->get('used_point');// 사용한 포인트
    //         // $items = $request->get('items');   ;//option_id, price, product_id , qty
    //         $options = $request->get('options');
    //         $imp_uid = '1113';//$allPostPutVars[impuid];  // 결제 식별번호(아임포트로부터 받은 결제 번호 이걸로 취소 할 수 있음
    //         $merchant_uid = '114';//$allPostPutVars[merchantuid]; // 가맹점 주문번호(우리주문번호)

    //         $time = date("Y-m-d H:i:s");
    //         $orderNo=date("Ymdhis").'_'.$user_id;

    //          $items =
    //               array([
    //                 "option_id" => 281,
    //                 'price'=> 14,
    //                 'product_id'=> 59,
    //                 'qty'=>'68'
    //               ],
    //               [
    //                 "option_id" => 280,
    //                 'price'=> 24,
    //                 'product_id'=> 59,
    //                 'qty'=>'78'
    //               ],
    //               [
    //                 "option_id" => '',
    //                 'price'=> 34,
    //                 'product_id'=> 59,
    //                 'qty'=>'178'
    //               ]);

    //             $options =
    //             array([
    //             "option_id" => 281,
    //             'price'=> 14,
    //             'product_id'=> 59,

    //             ],
    //             [
    //             "option_id" => 280,
    //             'price'=> 24,
    //             'product_id'=> 59,

    //             ],
    //             [
    //             "option_id" => '',
    //             'price'=> 34,
    //             'product_id'=> 59,

    //             ]);

    //         try {
    //             DB::beginTransaction();

    //             $order = DB::insert('
    //             INSERT into orders(created_at, updated_at, order_no, user_id, total_price)
    //                                     values(?, ?, ?, ?, ? ); ', array($time, $time, $orderNo , $user_id, $totalPrice)  ) ;

    //             DB::commit();

    //             $orderId = DB::select('select id from orders
    //                                     where user_id=? and order_no=? order by id desc limit 1; ', array($user_id, $orderNo)  ) ;

    //         } catch (Exception $e) {
    //             DB::rollBack();
    //             return exceped($e);
    //         }
    //         foreach ($items as $key3 => $value3) {
    //                     try {

    //                         DB::beginTransaction();
    //                         $product = DB::insert('INSERT into order_products(created_at, updated_at, order_id, product_id, qty)
    //                                                 VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $orderId[0]->id , $value3['product_id'], $value3['qty'])  ) ;

    //                         DB::commit();

    //                         $orderProduct = DB::select('select id, product_id, order_id, qty from order_products
    //                                                 where  order_id=? order by id desc limit 1 ; ', array($orderId[0]->id)  ) ;

    //                             // foreach ($options as $key => $value){ // 아이템 옵션리스트 선택된 1번옵션의 두번쨰
    //                             //     foreach ($orderProduct as $key2 => $value2) { // 주문서의 아이템정보
    //                             //         // print_r( $value['product_id'])    ;
    //                             //         if( $value['product_id']==$orderProduct[$key2]->product_id && $value3['product_id']==$orderProduct[$key2]->product_id  ){
    //                             //             if($value['option_id']){
    //                             //                 $option = DB::insert('INSERT into order_product_options(created_at, updated_at, order_product_id, product_option_id, price)
    //                             //                                 VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $orderProduct[$key2]->id , $value['option_id'], $value['price'] )) ;
    //                             //             DB::commit();
    //                             //             }
    //                             //         };
    //                             //     }
    //                             // }
    //                             foreach ($options as $key => $value){ // 아이템 옵션리스트 선택된 1번옵션의 두번쨰
    //                                 foreach ($orderProduct as $key2 => $value2) { // 주문서의 아이템정보
    //                                     // print_r( $value['product_id'])    ;
    //                                     if( $value3['product_id']==$orderProduct[$key2]->product_id ){
    //                                         if($value['option_id']){
    //                                             $option = DB::insert('INSERT into order_product_options(created_at, updated_at, order_product_id, product_option_id, price)
    //                                                             VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $orderProduct[$key2]->id , $value['option_id'], $key )) ;
    //                                         DB::commit();
    //                                         }
    //                                     };
    //                                 }
    //                             }

    //                     }
    //                     catch (Exception $e) {
    //                         DB::rollBack();
    //                         return exceped($e);
    //                     }


    //         } //end of foreach

    //         foreach ($orderProduct as $key4 => $value4) {
    //             $delivery = DB::insert('INSERT into order_product_deliveries(created_at, updated_at, order_product_id, qty, tracking_no, status)
    //                                 values(?, ?, ?, ?, ?, ?); ', array($time, $time, $orderProduct[$key4]->id , $orderProduct[$key4]->qty , 0 , 'request')  ) ;
    //                 DB::commit();
    //             }

    //                     $destination = DB::insert('INSERT into order_destinations(created_at, updated_at, order_id, user_id, post_code, address, address_detail, recipient_name )
    //                                         values(?, ?, ?, ?, ?, ?, ?, ? ); ', array($time, $time, $orderId[0]->id , $user_id,  $post_code, $address, $address_detail, $recipient_name)  ) ;
    //                     DB::commit();


    //                     return success([
    //                         'result' => true,     ]);

    // }

    //상품주문
    public function order_product(Request $request): array
    {
        $res = DB::transaction(function () use ($request) {
            $user_id = token()->uid;
            $phone = $request->get('receivePhone');
            $comment = $request->get('request');
            $post_code = $request->get('post_code');
            $address = $request->get('address');
            $address_detail = $request->get('address_detail'); //상세주소
            $recipient_name = $request->get('recipient_name');  // 받는사람 이름

            $use_point = $request->get('used_point');// 사용한 포인트

            $imp_id = $request->get('imp_id');  // 결제 식별번호(아임포트로부터 받은 결제 번호 이걸로 취소 할 수 있음
            $merchant_id = $request->get('merchantuid');

            $items = $request->get('items');  //option_id, price, product_id , qty

            // 주문 등록
            $order = Order::create([
                'order_no' => now()->timestamp . "_" . $user_id,
                'user_id' => $user_id,
                'total_price' => 0,
                'use_point' => $use_point,
                'imp_id' => $imp_id,
                'merchant_id' => $merchant_id,
            ]);

            $order->destination()->create([
                'post_code' => $post_code,
                'address' => $address,
                'address_detail' => $address_detail,
                'recipient_name' => $recipient_name,
                'phone' => $phone,
                'comment' => $comment,
            ]);

            // 주문하는 모든 상품 조회
            $products = Product::whereIn('id', \Arr::pluck($items, 'product_id'))->with('options')->get();

            // 배송비 추가한 브랜드 목록
            $ship_brands = [];

            // 주문 데이터 입력
            foreach ($items as $item) {
                // 해당 상품 조회
                $product = $products->firstWhere('id', $item['product_id']);

                if (is_null($product)) {
                    DB::rollBack();
                    return ['result' => false, 'message' => '주문하려는 제품에 오류가 있습니다.'];
                }

                // 브랜드별 배송비 입력
                if (!in_array($product->brand_id, $ship_brands)) {
                    $order->order_products()->create([
                        'brand_id' => $product->brand_id,
                        'qty' => 1,
                        'price' => $product->shipping_fee,
                    ]);
                    $ship_brands[] = $product->brand_id;
                    $order->total_price += $product->shipping_fee;
                }

                // 주문한 상품 입력
                $order_product = $order->order_products()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $product->sale_price * $item['qty'],
                ]);

                $order->total_price += $product->sale_price * $item['qty'];

                // 주문한 옵션 배열 생성
                $option_ids = [
                    $item['opt1'] ?? null,
                    $item['opt2'] ?? null,
                    $item['opt3'] ?? null,
                    $item['opt4'] ?? null,
                    $item['opt5'] ?? null,
                    $item['opt6'] ?? null,
                ];

                // 주문한 옵션 입력
                foreach ($option_ids as $option_id) {
                    // 현재 옵션 조회
                    $option = $product->options->firstWhere('id', $option_id);

                    if (is_null($option)) {
                        if (isset($option_id)) {
                            DB::rollBack();
                            return ['result' => false, 'message' => '주문하려는 옵션에 오류가 있습니다.'];
                        } else {
                            continue;
                        }
                    }

                    // 주문한 옵션 입력
                    $order_product->order_product_options()->create([
                        'product_option_id' => $option_id,
                        'price' => $option->price * $item['qty'],
                    ]);

                    $order->total_price += $option->price * $item['qty'];
                }

                if (Arr::has($item, 'cart_id')) {
                    $cart = Cart::where('id', $item['cart_id'])->first();
                    $cart->cart_options()->delete();
                    $cart->delete();
                }
            }

            // 포인트 사용한 경우 차감
            if ($use_point > 0) {
                PointController::change_point($user_id, $use_point * -1, 'order_use_point', 'order', $order->id);
                $order->total_price -= $use_point;
            }

            $order->save();

            CartOption::where(Cart::select('user_id')->whereColumn('id', 'cart_id'), $user_id)
                ->delete();
            Cart::where('user_id', $user_id)->delete();

            return ['result' => true];
        });

        return success($res);
    }

    //상품 상세정보내역 조회
    public function product_detail(Request $request): array
    {
        $user_id = token()->uid;
        $product_id = $request->get('product_id');

        try {
            DB::beginTransaction();

            $product_info = DB::select('SELECT shipping_fee, a.id as product_id , a.thumbnail_image, d.name_ko as brand_name, a.name_ko as product_name , a.price, a.sale_price, a.status,
                round((a.price-a.sale_price)/a.PRICE *100) as discount_rate,
                (select CASE WHEN count(product_id) >0 THEN "N" ELSE "N" END  FROM  carts  WHERE user_id=? and product_id= ? ) AS CART_YN
                from products a  left join  brands d on a.brand_id=d.id
                where
                  a.id=?  ; ', [$user_id, $product_id, $product_id]);

            $product_image = DB::select('select product_id, `order`, type, image  from product_images
                            where product_id= ? order by `order`, id; ', [$product_id]);


            $optionList1 = DB::select('select product_id, name_ko, id as option_id, price, status, `group` From product_options where product_id= ? and `group`=1 and deleted_at is null; ', [$product_id]);
            $optionList2 = DB::select('select product_id, name_ko, id as option_id, price, status, `group` From product_options where product_id= ? and `group`=2 and deleted_at is null; ', [$product_id]);
            $optionList3 = DB::select('select product_id, name_ko, id as option_id, price, status, `group` From product_options where product_id= ? and `group`=3 and deleted_at is null; ', [$product_id]);
            $optionList4 = DB::select('select product_id, name_ko, id as option_id, price, status, `group` From product_options where product_id= ? and `group`=4 and deleted_at is null; ', [$product_id]);
            $optionList5 = DB::select('select product_id, name_ko, id as option_id, price, status, `group` From product_options where product_id= ? and `group`=5 and deleted_at is null; ', [$product_id]);


            return success([
                'result' => true,
                'product_info' => $product_info,
                'product_image' => $product_image,
                'optionList1' => $optionList1,
                'optionList2' => $optionList2,
                'optionList3' => $optionList3,
                'optionList4' => $optionList4,
                'optionList5' => $optionList5,
            ]);


        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }

    }

    // 장바구니 내 품목 업데이트,삭제
    public function update_cart(Request $request): array
    {
        $user_id = token()->uid;
        $qty = $request->get('qty');
        $type = $request->get('type');
        $cart_id = $request->get('cart_id'); //option_Id, price
        $time = date("Y-m-d H:i:s");


        if ($type == 'qty') { //카트 내 상품 수량업데이트
            try {
                DB::beginTransaction();
                $cart = DB::update('UPDATE carts set qty=?, updated_at =?
                    where id=?; ', [$qty, $time, $cart_id]);

                DB::commit();
                return success([
                    'result' => true,
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }

        } elseif ($type == 'delete') {//카트상품옵션삭제,카트삭제

            try {
                DB::beginTransaction();
                $cart_option = DB::delete('delete from cart_options  where cart_id=?; ', [$cart_id]);
                $cart = DB::delete('delete from carts  where id=?; ', [$cart_id]);

                DB::commit();
                return success([
                    'result' => true,
                ]);

            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }
        }

        return success(['result' => false]);
    }

}
