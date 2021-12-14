@extends('layouts.admin')

@section('title', '푸시 관리 - 즉시발송')

@section('content')
    <form action="{{ route('admin.push.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px">
        @csrf
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">설명</span>
            <input type="text" name="description" id="description" style="flex: 1;">
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">대상</span>
            <select name="target" id="target" style="flex: 1;">
                <option value="all">전체</option>
                <option value="mission" disabled>미션 참가자</option>
                <option value="user" disabled>지정 유저</option>
            </select>
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">제목</span>
            <input name="title" value="써클인" style="flex: 1;">
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">내용</span>
            <textarea name="message" class="board" style="flex: 1; resize: none;" rows="10"></textarea>
        </label>
        <div style="display: flex; flex-direction: row">
            <span style="width: 100px">푸시 시점</span>
            <div style="display: flex; flex: 1; gap: 8px">
                <label style="display: flex; align-items: center">
                    <span style="width: 100px">푸시 일자<br>(비워두면 매일)</span>
                    <input type="date" name="send_date" value="{{ date('Y-m-d') }}">
                </label>
                <label style="display: flex; align-items: center">
                    <span style="width: 100px">푸시 시간</span>
                    <input type="time" name="send_time" value="{{ date('H:i') }}">
                </label>
            </div>
        </div>
        <button class="btn">예약</button>
    </form>
@endsection
