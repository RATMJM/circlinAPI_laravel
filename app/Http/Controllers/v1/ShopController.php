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


 
  public function cart_list(Request $request): array
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
                
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 0,1) as price1,
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 1,1) as price2,
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 2,1) as price3,
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 3,1) as price4,
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 4,1) as price5,
                (select price from product_options x, cart_options y where x.id= y.product_option_id and a.id=y.cart_id limit 5,1) as price6
                       
                         
            
               from carts a, 
               products c, 
               brands d, 
               users e  

               where a.user_id="1"    
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
}
