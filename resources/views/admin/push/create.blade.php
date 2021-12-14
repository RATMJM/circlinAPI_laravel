@extends('layouts.admin')

@section('title', '푸시 관리 - 즉시발송')

@section('content')
    <form action="{{ route('admin.push.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px">
        @csrf
        <label style="display: flex; align-items: center;">
            <span style="width: 50px">대상</span>
            <select name="target" id="target" style="flex: 1;">
                <option value="all">전체</option>
                <option value="mission" disabled>미션 참가자</option>
                <option value="user" disabled>지정 유저</option>
            </select>
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 50px">제목</span>
            <input name="title" value="써클인" style="flex: 1;">
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 50px">내용</span>
            <textarea name="content" class="board" style="flex: 1; resize: none;" rows="10"></textarea>
        </label>
        <button class="btn">전송</button>
    </form>
@endsection
