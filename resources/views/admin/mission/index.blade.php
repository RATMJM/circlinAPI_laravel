@extends('layouts.admin')

@section('title', '미션')

@section('content')
    <table>
        <thead>
        <tr>
            <th>전체 미션 수</th>
            <th>금일 제작 미션 수</th>
            <th>금주 제작 미션 수</th>
            <th>금월 제작 미션 수</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ number_format($missions_count['all']) }}</td>
            <td>{{ number_format($missions_count['day']) }}</td>
            <td>{{ number_format($missions_count['week']) }}</td>
            <td>{{ number_format($missions_count['month']) }}</td>
        </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'all']])) }}" class="btn">
        전체
    </a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'day']])) }}" class="btn">
        금일
    </a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'week']])) }}" class="btn">
        금주
    </a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'month']])) }}" class="btn">
        금월
    </a>
    <br>
    <br>
    <br>
    <label for="type">미션 특성</label>
    <select name="type" id="type" onchange="typeChange(event)">
        <option value="all" {{ request()->get('type', 'all') === 'all' ? 'selected' : '' }}>전체</option>
        <option value="normal" {{ request()->get('type', 'all') === 'normal' ? 'selected' : '' }}>일반</option>
        <option value="event" {{ request()->get('type', 'all') === 'event' ? 'selected' : '' }}>이벤트</option>
    </select>
    <br>
    <form action="{{ route('admin.mission.index') }}">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <select name="search_type">
            <option value="all" {{ request()->get('search_type', 'all') === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일, 미션명)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($missions->total()) }}</b> 개</p>
    <br>
    <div class="table">
        <div class="row head">
            <div class="col">ID</div>
            <div class="col">카테고리</div>
            <div class="col" style="grid-column: 3/span 2">미션명</div>
            <div class="col">미션 특성</div>
            <div class="col">제작자</div>
            <div class="col">제작일</div>
        </div>
        @foreach ($missions as $mission)
            <a class="row" href="{{ route('admin.mission.show', ['mission_id' => $mission->id]) }}">
                <div class="col">{{ $mission->id }}</div>
                <div class="col center">{{ $mission->category }}</div>
                <div class="col"><img src="{{ $mission->thumbnail_image }}" alt=""></div>
                <div class="col">{{ $mission->title }}</div>
                <div class="col center">{!! $mission->is_event ? '<b style="font-size:1.2rem">이벤트</b>' : '일반' !!}</div>
                <div class="col center">{{ $mission->nickname }}<br>({{ $mission->email }})</div>
                <div class="col center">{{ $mission->created_at }}</div>
            </a>
        @endforeach
    </div>
    <br>
    <div class="center">{{ $missions->withQueryString()->links() }}</div>

    <style>
        .row {
            grid-template-columns: 60px 120px 300px 1fr 100px 200px 150px;
        }
    </style>

    <script>
        function typeChange(e) {
            location.href = `?{{ Arr::query(Arr::except(request()->query(), 'type')) }}&type=` + e.target.value;
        }
    </script>
@endsection
