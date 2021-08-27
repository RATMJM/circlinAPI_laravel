<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function category_banner($category_id): array
    {
        return [
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/00_fake_banner01.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/00_fake_banner02.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/KRKO_3UP_STATIC_1000x625_BAN_WATCH_NA_STA_BNOW_NA.jpg',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/20210809-challenge-habit-mymealprotein3-app-banner-ad-h.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/20210809-challenge-habit-arginine3-app-banner-ad-h.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/20210802-challenge-run-815virtual-app-banner-ad-h.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://cyld20183.speedgabia.com/Image/BANNER/20210722-challenge-habbit-Growjee-app-banner-ad-h.png',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
        ]; // 더미데이터
    }
}
