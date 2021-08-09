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
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
            [
                'banner_image' => 'https://via.placeholder.com/1500x750',
                'link_type' => 'url',
                'link_url' => 'https://via.placeholder.com/1500x750',
            ],
        ]; // 더미데이터
    }
}
