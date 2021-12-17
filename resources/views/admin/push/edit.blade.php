@extends('layouts.admin')

@section('title', '푸시 관리 - 즉시발송')

@section('content')
    <form action="{{ route('admin.push.update', $data->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 16px">
        @method('PATCH')
        @csrf
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">설명</span>
            <input type="text" name="description" id="description" style="flex: 1;" value="{{ $data->description }}">
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">대상</span>
            <select name="target" id="target" style="flex: 1;">
                <option value="all" {{ $data->target === 'all' ? 'selected' : '' }}>전체</option>
                <option value="mission" {{ $data->target === 'mission' ? 'selected' : '' }}>미션 참가자</option>
                <option value="user" {{ $data->target === 'user' ? 'selected' : '' }}>지정 유저</option>
            </select>
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">대상 ID<br>(구분자 : " | ")</span>
            <div style="display: flex; flex: 1; flex-direction: column; gap: 8px;">
                <input type="text" name="target_ids" id="target_ids" value="{{ $data->target_ids }}">
                <div style="display: flex; gap: 8px;">
                    <span style="font-weight: bold;">검색하기</span>
                    <a href="{{ route('admin.mission.index') }}" target="_blank">미션</a>
                    <a href="{{ route('admin.user.index') }}" target="_blank">유저</a>
                </div>
            </div>
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">제목</span>
            <input name="title" value="{{ $data->title }}" style="flex: 1;">
        </label>
        <label style="display: flex; align-items: center;">
            <span style="width: 100px">내용</span>
            <textarea name="message" class="board" style="flex: 1; resize: none;" rows="10">{{ $data->message }}</textarea>
        </label>
        <div style="display: flex; flex-direction: row">
            <span style="width: 100px">푸시 시점</span>
            <div style="display: flex; flex: 1; gap: 8px">
                <label style="display: flex; align-items: center">
                    <span style="width: 100px">푸시 일자<br>(비워두면 매일)</span>
                    <input type="date" name="send_date" value="{{ date('Y-m-d', strtotime($data->send_date)) }}">
                </label>
                <label style="display: flex; align-items: center">
                    <span style="width: 100px">푸시 시간</span>
                    <input type="time" name="send_time" value="{{ date('H:i', strtotime($data->send_time)) }}">
                </label>
            </div>
        </div>
        <button class="btn">수정</button>
    </form>
@endsection
