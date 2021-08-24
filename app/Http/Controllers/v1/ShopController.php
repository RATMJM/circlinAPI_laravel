<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
  
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function item_list(Request $request): array
      {   
        //  $user_id = '4';//token()->uid;  
        //  $category = '2';//$request->get('category'); // 카테고리 
        //  $type = 'high';//$request->get('type'); // 필터값
        $user_id = token()->uid;  
         $category = $request->get('category'); // 카테고리 
         $type = $request->get('type'); // 필터값
         try {
            DB::beginTransaction();
        if($category=='전체'){
            if ($type=="hot") {
             
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id  
                order by status ;'   , array('1')) ;
  
            }else if($type=="high") {
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                order by sale_price desc;'   , array('1')) ;
            } 
            else if($type=="low") {
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                order by sale_price ;'   , array('1')) ;
            }else{
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                order by status;'   , array('1')) ;
            } 
        }else{// ㅋㅏ테고리 눌렀을떄
            if ($type=="hot") {
             
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b 
                WHERE  
                is_show= ?
                and  a.product_category_id=?
                and b.id=a.brand_id;'   , array('1', $category)) ;
 
                
                
            }else if($type=="high") {
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=? 
                order by sale_price desc;'   , array('1', $category)) ;
            } 
            else if($type=="low") {
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=? 
                order by sale_price  ;'    , array('1', $category)) ;
            }else{
               
                $itemList = DB::select('select case when shipping_fee > 0 then "Y" else "N" end as SHIP_FREE_YN,
                code ,
                thumbnail_image,
                b.name_ko,
                a.name_ko as prod_name,
                price, sale_price,
                round((a.PRICE-a.sale_PRICE)/a.PRICE *100) as discount_rate,
                a.status
                FROM products a, brands b  
                WHERE  
                is_show= ?
                and b.id=a.brand_id
                and  a.product_category_id=? 
                order by status;'   , array('1', $category)) ;
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


    public function shop_category(Request $request): array
      {   
 
         try {
            DB::beginTransaction();
            
                $categoryList = DB::select('select id as product_category_id, title   
                                            from  product_categories b
                                            where deleted_at is null;'  ) ;
   
             
            return success([
                'result' => true,
                'categoryList' => $categoryList, 
            ]);
 
           
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
 
    }
 
    public function shop_banner(): array
    {   
 
         try {
            DB::beginTransaction();
            
                $shopBannerList = DB::select('select b.image, b.product_id, b.link_url  From products a , banners b
                where   date_add(sysdate(), interval 9 hour) between  b.started_at and b.ended_at
                and b.product_id=a.id and b.deleted_at is null
                order by sort_num;;'  ) ;

            return success([
                'result' => true,
                'shopBannerList' => $shopBannerList, 
            ]);
    
        } catch (Exception $e) {
            DB::rollBack();
            return exceped($e);
        }
 
    }

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
                DUAL;', array($user_id, $user_id)  ) ;
              

                $shopPointList = DB::select('SELECT created_at, point, user_id, reason from point_histories
                where user_id= ?
                order by created_at desc;', array($user_id)  ) ;
  
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
 

   public function bought_product_list(Request $request): array
    {   
        $user_id = token()->uid;
        
       try {
          DB::beginTransaction();
          
                $orderList = DB::select('select
                ORDER_NO, a.total_price, f.name_ko as product_name, g.name_ko as brand_name, f.thumbnail_image, f.code, a.created_at as order_time,
                h.id as feed_product_id,  "" SELECT_YN, b.qty, e.status,
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
                LEFT JOIN (SELECT name_ko, order_product_id FROM order_product_options a, product_options b where b.id=a.product_option_id limit 5,1 ) opt6 ON opt6.order_product_id=b.id,
                
                order_destinations d,
                order_product_deliveries e,
                products f LEFT JOIN feed_products h ON f.id=h.product_id ,
                brands g
                where
                a.id=b.order_id
                and a.id=d.order_id
                and b.id=e.order_product_id
                and f.id=b.product_id
                and f.brand_id = g.id
                and a.user_id=?
                ;', array($user_id)  ) ;
              

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
                    $buyPointInfo='';
  
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


 
  public function cart_list(Reqsuest $request): array
  {   
      $user_id = token()->uid;
      
     try {
        DB::beginTransaction();
        
        $cartList = DB::select(' 
        select 
          
                c.thumbnail_image    , e.nickname, e.id, a.qty , c.name_ko as product_name, sale_price,  
                    c.id , c.status, c.shipping_fee,
                
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1) as opt1,
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1) as opt2,
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1) as opt3,
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1) as opt4,
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1) as opt5,
                (select name_ko from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1) as opt6,
                
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1) as price1,
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1) as price2,
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1) as price3,
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1) as price4,
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1) as price5,
                (select x.price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1) as price6
                       
                         
            
               from carts a, 
               products c, 
               brands d, 
               users e  

               where a.user_id=?    
               and a.product_id=c.id
               and d.user_id=e.id
               and c.brand_id=d.id; ', array($user_id)  ) ;
            
 
              return success([
              'result' => true,
              'cartList' => $cartList,                 
        ]);

       
    } catch (Exception $e) {
        DB::rollBack();
        return exceped($e);
    }

    }


    public function cart(Request $request): array
    {   
            $user_id = '1';//token()->uid;
            
            $useCarePoint = '99';//$allPostPutVars[usePoint];

            $orderName = 'pname';//; $allPostPutVars[orderName];
            $orderPhone = 'ordph';//

            $receiveName = 'rcvName'; 
            $receivePhone = 'rcvPh';
            $receiveAddress = 'rcvAddr';
            $addressDetail = 'addrDet';
            $postCode = 'postcd';

            $amount = '99999'; // 총금
            $shipFee ='3000';
            $totalPrice = '999999991'; // 총액
            $request = 'requests';
            $items = '';// $allPostPutVars[items]; //결제된 아이템들 배열 구성요소는 똑같음 변동은 그 안에 QTY, 성공 실패 YN 변경 php에서 포이치로 인서트해야함
            // optionId price recipient_name post_code  address address_detail           
            // $price='99912';
            // $productId = '59';//$items
            // $qty
            $qty ='11'; //$items
            // $amount = $allPostPutVars[amount];  // 결제 된 총액
            $imp_uid = '1113';//$allPostPutVars[impuid];  // 결제 식별번호(아임포트로부터 받은 결제 번호 이걸로 취소 할 수 있음
            $merchant_uid = '114';//$allPostPutVars[merchantuid]; // 가맹점 주문번호(우리주문번호)
            // $option1 = $allPostPutVars[option1];// COMMON_CODE 의 SEQ
            $time = date("Y-m-d H:i:s");
            $cartNo = date("Ymdhis").'_'.$user_id; 
        
            try {
                DB::beginTransaction();        
                $cart = DB::insert('INSERT into carts(created_at, updated_at, user_id, product_id,  qty)
                                        VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $user_id, $productId, $qty)  ) ;
                    
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }

            if($cart>0){
                try {
                    DB::beginTransaction();
                    $cartId = DB::select('select id from carts
                                            where user_id=? order by id desc  limit 0,1 ; ', array($user_id)  ) ;
                                        
                } catch (Exception $e) {
                    DB::rollBack();
                    return exceped($e);
                }
                
                try {
             
                    foreach ($items as $key => $value){  
                        
                        $option = DB::insert('INSERT into cart_options(created_at, updated_at, cart_id, product_option_id, price)
                                            VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $cartId[0]->id , $items[$key]->optionId, $items[$key]->$price)) ;            
                        DB::commit();
    
                    }
       
                }
                catch (Exception $e) {
                    DB::rollBack();
                    return exceped($e);
                }

            }else{
                return false;
            }
            
    }


    public function order_product(Request $request): array
    {   
            $user_id = '1';//token()->uid;
            
            $useCarePoint = '99';//$allPostPutVars[usePoint];

            $orderName = 'pname';//; $allPostPutVars[orderName];
            $orderPhone = 'ordph';//

            $receiveName = 'rcvName'; 
            $receivePhone = 'rcvPh';
            $receiveAddress = 'rcvAddr';
            $addressDetail = 'addrDet';
            $postCode = 'postcd';

            $amount = '99999'; // 총금
            $shipFee ='3000';
            $totalPrice = '999999991'; // 총액
            $request = 'requests';
            $items = '';// $allPostPutVars[items]; //결제된 아이템들 배열 구성요소는 똑같음 변동은 그 안에 QTY, 성공 실패 YN 변경 php에서 포이치로 인서트해야함
            // optionId price recipient_name post_code  address address_detail           
            // $price='99912';
            // $productId = '59';//$items
            // $qty
            $qty ='11'; //$items
            // $amount = $allPostPutVars[amount];  // 결제 된 총액
            $imp_uid = '1113';//$allPostPutVars[impuid];  // 결제 식별번호(아임포트로부터 받은 결제 번호 이걸로 취소 할 수 있음
            $merchant_uid = '114';//$allPostPutVars[merchantuid]; // 가맹점 주문번호(우리주문번호)
            // $option1 = $allPostPutVars[option1];// COMMON_CODE 의 SEQ
            $time = date("Y-m-d H:i:s");
            $orderNo=date("Ymdhis").'_'.$user_id; 
        

            try {
                DB::beginTransaction();
                
                $order = DB::insert(' 
                INSERT into orders(created_at, updated_at, order_no, user_id, total_price)
                                        values(?, ?, ?, ?, ? ); ', array($time, $time, $orderNo , $user_id, $totalPrice)  ) ;
                    
                DB::commit();
            
                $orderId = DB::select('select id from orders
                                        where user_id=? and order_no=? ; ', array($user_id, $orderNo)  ) ;
                                    
            } catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            }
            
            try {
                DB::beginTransaction();        
                $product = DB::insert('INSERT into order_products(created_at, updated_at, order_id, product_id, qty)
                                        VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $orderId[0]->id , $productId, $qty)  ) ;
                    
                DB::commit();
                
                $orderProduct = DB::select('select id, product_id, order_id from order_products
                                        where  order_id=64 ; ',);
                                        //array($orderId[0]->id)  ) ;
                        
                        
                    //     echo $orderProductId[0]->id;
 
                    // foreach($orderProduct as $key2 => $value)
                    // {
                    //     echo $orderProduct[$key2]->product_id ."<br/>";
                    // }
                    //foreach($items as $key => $value)
                    // {
                    //     echo $items[$key]->product_id ."<br/>";
                    // }
        
                    foreach ($items as $key => $value){ // 아이템 옵션리스트 선택된 1번옵션의 두번쨰
                        foreach ($orderProduct as $key2 => $value) { // 주문서의 아이템정보

                            if($item[$key]->productId = $orderProduct[$key2]->product_id ){ //선택된 옵션의 상품코드=주문서의 상품코드 같을때 orderProduct의 ID 입력
                                $option = DB::insert('INSERT into order_product_options(created_at, updated_at, order_product_id, product_option_id, price)
                                                    VALUES(?, ?, ?, ?, ? ); ', array($time, $time, $orderProduct[$key2]->id , $items[$key]->optionId, $items[$key]->$price)) ;            
                                DB::commit();

                                $delivery = DB::insert('INSERT into order_product_deliveries(created_at, updated_at, order_product_id, qty)
                                                    values(?, ?, ?, ? ); ', array($time, $time, $orderProduct[$key2]->id,  $orderProduct[$key2]->$qty)  ) ;
                                DB::commit();

                                


                            }
                        }
                    }
 
                    
                    $destination = DB::insert('INSERT into order_destinations(created_at, updated_at, order_id, user_id, post_code, address, address_detail, recipient_name )
                                                    values(?, ?, ?, ?, ?, ?, ?, ? ); ', array($time, $time, $orderId[0]->id , $user_id,  $items[0]->post_code, $items[0]->address, $items[0]->address_detail, $items[$key]->$recipient_name)  ) ;
                                DB::commit();

                    
                                return success([
                                    'result' => true,     ]);
            }
            catch (Exception $e) {
                DB::rollBack();
                return exceped($e);
            } 
    }

 
    public function product_detail(Request $request): array
    {   
        $user_id = token()->uid; 
        $product_id = $request->get('product_id'); 
        
            try {
                DB::beginTransaction();
                
                $product_info = DB::select('SELECT shipping_fee, a.id as product_id , c.thumbnail_image, d.name_ko as BRAND_NAME, a.name_ko as PRODUCT_NAME , a.price, a.sale_price, a.status
                                        from products a, product_images c , brands d
                                        where  a.brand_id=d.id and a.id=c.id 
                                        and a.id=?; ', array($product_id)  ) ;
                    
                $product_image = DB::select('select product_id, `order`, type, image  from product_images 
                            where product_id= ? ; ', array($product_id)  ) ; 


                $optionList1 = DB::select('select * From product_options where product_id= ? and `group`=1 ; ', array($product_id)  ) ; 
                $optionList2 = DB::select('select * From product_options where product_id= ? and `group`=2 ; ', array($product_id)  ) ; 
                $optionList3 = DB::select('select * From product_options where product_id= ? and `group`=3 ; ', array($product_id)  ) ; 
                $optionList4 = DB::select('select * From product_options where product_id= ? and `group`=4 ; ', array($product_id)  ) ; 
                $optionList5 = DB::select('select * From product_options where product_id= ? and `group`=5 ; ', array($product_id)  ) ; 

 

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

    

}
