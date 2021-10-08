@extends('layouts.push')

@section('title', '푸시 관리')

@section('content')
    @parent
    <form action="{{ route('admin.push.index') }}">
        <input type="hidden" name="filter" value="{{ request()->get('filter') }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (설명, 타이틀, 내용)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($data->total()) }}</b> 개</p>
    <br>
    {{--<p><a href="{{ route('admin.push.create') }}" class="btn">예약하기</a></p>--}}
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px">번호</th>
            <th style="width: 160px">대상</th>
            <th style="width: 200px">타이틀</th>
            <th style="width: auto">내용</th>
            <th style="width: 200px">설명</th>
            <th style="width: 100px">발송 일자<br>(없으면 매일 반복)</th>
            <th style="width: 100px">발송 시간</th>
            <th style="width: 300px">푸시 링크</th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->target }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->message }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->send_date }}</td>
                <td>{{ $item->send_time }}</td>
                <td>{{ $item->link_type }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center">예약된 푸시가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $data->withQueryString()->links() }}</div>
@endsection
