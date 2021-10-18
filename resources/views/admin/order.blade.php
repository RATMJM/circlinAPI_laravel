@extends('layouts.admin')

@section('title', '주문 통계')

@section('content')
    {{--<table>
        <thead>
        <tr>
            <th>전체 회원 수</th>
            <th>금일 가입자 수</th>
            <th>금주 가입자 수</th>
            <th>금월 가입자 수</th>
            <th>5.5 이전 탈퇴자 수</th>
            <th>5.5 이후 탈퇴자 수</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ number_format($orders_count['all']) }}</td>
            <td>{{ number_format($orders_count['day']) }}</td>
            <td>{{ number_format($orders_count['week']) }}</td>
            <td>{{ number_format($orders_count['month']) }}</td>
            <td>{{ number_format($deleted_old_users_count) }}</td>
            <td>{{ number_format($deleted_users_count) }}</td>
        </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>--}}
    <form action="{{ route('admin.order.index') }}">
        <input type="hidden" name="filter" value="{{ request()->get('filter') }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($orders->total()) }}</b> 개</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 160px">주문번호</th>
            <th style="width: 200px">닉네임<br>(이메일)</th>
            <th style="width: auto">주소</th>
            <th style="width: 115px">수령인<br>(수령인 번호)</th>
            <th style="width: 250px">요청사항</th>
            <th style="width: 80px">결제금액<br>(포인트)</th>
            <th style="width: 300px">[브랜드] 제품명<br>단품금액 (주문수량)</th>
            <th style="width: 150px">옵션명</th>
            <th style="width: 120px">택배사<br>송장번호<br>(발송수량)</th>
            <th style="width: 80px">배송여부</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            @php($rowspan = max(count($order->order_products->whereNotNull('product_id')->pluck('id')->unique()) +
                    count($order->order_products->whereNotNull('ship_brand_id')->pluck('id')->unique()), 1))
            <tr style="border-top: 2px solid #000">
                <td rowspan="{{ $rowspan }}">{{ $order->order_no }}<br>({{ $order->created_at }})<br>({{ $order->id }})</td>
                <td rowspan="{{ $rowspan }}">
                    {{ $order->nickname }}
                    <br>({{ $order->email }})
                </td>
                <td rowspan="{{ $rowspan }}">({{ $order->post_code }}) {{ $order->address }} {{ $order->address_detail }}</td>
                <td rowspan="{{ $rowspan }}">
                    {{ $order->recipient_name }}
                    <br>({{ $order->phone }})
                </td>
                <td rowspan="{{ $rowspan }}">{{ $order->comment }}</td>
                <td rowspan="{{ $rowspan }}" class="center">
                    {{ number_format($order->total_price) }}<br>({{ number_format($order->use_point) }})
                </td>
                @foreach($order->order_products->whereNotNull('ship_brand_id') as $ship_brand)
                    <td>
                        <b>[{{ $ship_brand->ship_brand_name }}]</b> 배송비
                        <br>{{ number_format($ship_brand->product_price) }}
                    </td>
                    <td class="center"></td>
                    <td class="center"></td>
                    <td class="center"></td>
                    {!! ($loop->last && count($order->order_products->whereNotNull('product_id')->groupBy('product_id')) == 0) ? '' : '</tr></tr>' !!}
                @endforeach
                @forelse($order->order_products->whereNotNull('product_id')->groupBy('id') as $products)
                        <td>
                            <b>[{{ $products[0]->brand_name }}]</b> {{ $products[0]->product_name }}
                            <br>{{ number_format($products[0]->product_price) }} ({{ $products[0]->qty }})
                        </td>
                        <td>
                            @foreach($products as $option)
                                {{ $option->option_name }}{{ $loop->last ? '' : ' / ' }}
                            @endforeach
                        </td>
                        <td>@if($products[0]->company){{ $products[0]->company }}<br>{{ $products[0]->tracking_no }}<br>({{ $products[0]->delivery_qty }})@endif</td>
                        <td class="center">
                            @if($products[0]->company)
                                @if($products[0]->completed_at)
                                    <span style="color:red">배송완료</span>
                                @else
                                    <span style="color:blue">배송중</span>
                                @endif
                            @endif
                        </td>
                    {!! $loop->last ? '' : '</tr></tr>' !!}
                @empty
                    <td colspan="4"></td>
                @endforelse
                </tr>
            @empty
                <tr>
                    <td colspan="0" class="center">주문이 없습니다.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $orders->withQueryString()->links() }}</div>
@endsection
