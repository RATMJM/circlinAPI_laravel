@extends('layouts.admin')

@section('title', '피드 통계')

@section('content')
    <table>
        <thead>
        <tr>
            <th>전체 피드 수</th>
            <th>금일 작성 피드 수</th>
            <th>금주 작성 피드 수</th>
            <th>금월 작성 피드 수</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ number_format($feeds_count['all']) }}</td>
            <td>{{ number_format($feeds_count['day']) }}</td>
            <td>{{ number_format($feeds_count['week']) }}</td>
            <td>{{ number_format($feeds_count['month']) }}</td>
        </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <a href="{{ route('admin.feed.index', Arr::collapse([request()->all(), ['filter' => 'all']])) }}" class="btn">전체</a>
    <a href="{{ route('admin.feed.index', Arr::collapse([request()->all(), ['filter' => 'day']])) }}" class="btn">금일</a>
    <a href="{{ route('admin.feed.index', Arr::collapse([request()->all(), ['filter' => 'week']])) }}" class="btn">금주</a>
    <a href="{{ route('admin.feed.index', Arr::collapse([request()->all(), ['filter' => 'month']])) }}" class="btn">금월</a>
    <br>
    <br>
    <br>
    <form action="{{ route('admin.feed.index') }}">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일, 미션명)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($feeds->total()) }}</b> 개</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px" rowspan="2">ID</th>
            <th style="width: 300px" rowspan="2">이미지</th>
            <th style="width: auto" rowspan="2">내용</th>
            <th style="width: 120px" rowspan="2">카테고리</th>
            <th style="width: 300px">미션명</th>
            <th style="width: 200px">작성자</th>
            <th style="width: 150px" rowspan="2">작성일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($feeds as $feed)
            <tr>
                <td rowspan="{{ max(count($feed->missions), 1) }}" class="center">
                    {{ $feed->id }}
                    <br>{{ $feed->is_hidden ? '🔒︎' : '' }}
                </td>
                <td rowspan="{{ max(count($feed->missions), 1) }}">
                    @foreach($feed->images as $image)
                        @if($image->type === 'image')
                            <img src="{{ $image->image }}" alt="" width="100%">
                        @else
                            <video src="{{ $image->image }}" width="100%" height="300px" controls></video>
                        @endif
                    @endforeach
                </td>
                <td rowspan="{{ max(count($feed->missions), 1) }}">
                    {!! nl2br($feed->content) !!}
                </td>
                @if(count($feed->missions) > 0)
                    <td>{{ $feed->missions[0]->emoji }} {{ $feed->missions[0]->category }}</td>
                    <td>{{ $feed->missions[0]->title }}</td>
                @else
                    <td colspan="2" class="center">미션이 없습니다.</td>
                @endif
                <td rowspan="{{ max(count($feed->missions), 1) }}">{{ $feed->nickname }}<br>({{ $feed->email }})</td>
                <td rowspan="{{ max(count($feed->missions), 1) }}" class="center">{{ $feed->created_at }}</td>
            </tr>
            @foreach($feed->missions as $mission)
                @if($loop->first) @continue @endif
                <tr>
                    <td>{{ $mission->emoji }} {{ $mission->category }}</td>
                    <td>{{ $mission->title }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="0" class="center">피드가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $feeds->withQueryString()->links() }}</div>
@endsection
