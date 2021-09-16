@extends('layouts.admin')

@section('title', '유저 통계')

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
            <th style="width: 100px">닉네임<br>(이메일)</th>
            <th style="width: 150px">제품명<br>옵션명<br>(주문수량)</th>
            <th style="width: auto">주소</th>
            <th style="width: 115px">수령인<br>(수령인 번호)</th>
            <th style="width: auto">요청사항</th>
            <th style="width: 120px">택배사<br>송장번호<br>(발송수량)</th>
            <th style="width: 80px">배송여부</th>
        </tr>
        </thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td>{{ $order->order_no }}</td>
                <td>
                    {{ $order->nickname }}
                    <br>({{ $order->email }})
                </td>
                <td><b>{{ $order->product_name }}</b><br>{{ $order->option_name }}<br>({{ $order->qty }})</td>
                <td>({{ $order->post_code }}) {{ $order->address }} {{ $order->address_detail }}</td>
                <td>{{ $order->recipient_name }}<br>({{ $order->phone }})</td>
                <td>{{ $order->comment }}</td>
                <td>@if($order->company){{ $order->company }}<br>{{ $order->tracking_no }}<br>({{ $order->delivery_qty }})@endif</td>
                <td style="text-align: center">
                    {!! $order->completed_at ? '<span style="color:red">배송완료</span>' : '<span style="color:blue">배송중</span>' !!}
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
