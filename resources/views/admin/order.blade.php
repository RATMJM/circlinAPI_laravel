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
            <option value="all" selected>전체 (닉네임, 이메일)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요.">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($orders->total()) }}</b> 명</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 160px">주문번호</th>
            <th style="width: 200px">닉네임<br>(이메일)</th>
            <th style="width: auto">주소</th>
            <th style="width: 115px">수령인<br>(수령인 번호)</th>
            <th style="width: 250px">요청사항</th>
            <th style="width: 400px">제품명<br>(주문수량)</th>
            <th style="width: 150px">옵션명</th>
            <th style="width: 120px">택배사<br>송장번호<br>(발송수량)</th>
            <th style="width: 80px">배송여부</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders->groupBy('id') as $order)
            <tr>
                <td rowspan="{{ 1 && count($order->pluck('product_id')->unique()) }}">{{ $order[0]->order_no }}</td>
                <td rowspan="{{ 1 && count($order->pluck('product_id')->unique()) }}">
                    {{ $order[0]->nickname }}
                    <br>({{ $order[0]->email }})
                </td>
                <td rowspan="{{ 1 && count($order->pluck('product_id')->unique()) }}">({{ $order[0]->post_code }}) {{ $order[0]->address }} {{ $order[0]->address_detail }}</td>
                <td rowspan="{{ 1 && count($order->pluck('product_id')->unique()) }}">
                    {{ $order[0]->recipient_name }}
                    <br>({{ $order[0]->phone }})
                </td>
                <td rowspan="{{ 1 && count($order->pluck('product_id')->unique()) }}">{{ $order[0]->comment }}</td>
                <td style="padding:0" colspan="4">
                    <table style="border: 0">
                        <tbody>
                        @foreach($order->groupBy('product_id') as $products)
                            <tr style="{{ $loop->first ? '' : 'border-top: 1px solid #000' }}">
                                <td style="width: 400px; background: inherit; border: 0;">
                                    <b>{{ $order[0]->product_name }}</b>
                                    <br>({{ $order[0]->qty }})
                                </td>
                                <td style="width: 150px; background: inherit; border-top: 0; border-bottom: 0; border-right: 0;">
                                    @foreach($products as $option)
                                        {{ $option->option_name }}{{ $loop->last ? '' : ' / ' }}
                                    @endforeach
                                </td>
                                <td style="width: 120px; background: inherit; border-top: 0; border-bottom: 0; border-right: 0;">@if($products[0]->company){{ $products[0]->company }}<br>{{ $products[0]->tracking_no }}<br>({{ $products[0]->delivery_qty }})@endif</td>
                                <td style="width: 80px; background: inherit; border-top: 0; border-bottom: 0; border-right: 0; text-align: center">
                                    @if($products[0]->company)
                                        @if($products[0]->completed_at)
                                            <span style="color:red">배송완료</span>
                                        @else
                                            <span style="color:blue">배송중</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center">유저가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div style="text-align: center">{{ $orders->withQUeryString()->links() }}</div>
@endsection
