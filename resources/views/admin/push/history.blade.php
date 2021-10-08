@extends('layouts.push')

@section('title', '푸시 관리')

@section('content')
    @parent
    <form action="{{ route('admin.push.history') }}">
        <input type="hidden" name="filter" value="{{ request()->get('filter') }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일, 타이틀, 내용)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($data->total()) }}</b> 개</p>
    <br>
    <p><a href="{{ route('admin.push.create') }}" class="btn">예약하기</a></p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px">번호</th>
            <th style="width: 160px">대상</th>
            <th style="width: 200px">타이틀</th>
            <th style="width: auto">내용</th>
            <th style="width: 135px">발송일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $item)
            <tr>
                <td class="center">{{ $item->id }}</td>
                <td>{{ $item->nickname }}<br>({{ $item->email }})</td>
                <td class="center">{{ $item->title }}</td>
                <td>{{ rn_to_br($item->message) }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="center">푸시 내역이 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $data->withQueryString()->links() }}</div>
@endsection
