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
 
}
