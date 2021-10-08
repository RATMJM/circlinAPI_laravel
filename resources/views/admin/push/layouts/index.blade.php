@extends('layouts.admin')

@section('content')
    <ul id="nav">
        <li><a href="{{ route('admin.push.reservation') }}">푸시 예약</a></li>
        <li><a href="{{ route('admin.push.history') }}">푸시 전송 내역</a></li>
    </ul>
@endsection
