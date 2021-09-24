@extends('layouts.admin')

@section('title', '제작한 미션 통계')

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
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'all']])) }}" class="btn">전체</a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'day']])) }}" class="btn">금일</a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'week']])) }}" class="btn">금주</a>
    <a href="{{ route('admin.mission.index', Arr::collapse([request()->all(), ['filter' => 'month']])) }}" class="btn">금월</a>
    <br>
    <br>
    <br>
    <form action="{{ route('admin.mission.index') }}">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일, 미션명)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($missions->total()) }}</b> 개</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 60px">ID</th>
            <th style="width: 120px">카테고리</th>
            <th style="width: 160px">썸네일 이미지</th>
            <th style="width: 300px">미션명</th>
            <th style="width: auto">상세 내용</th>
            <th style="width: 150px">지역</th>
            <th style="width: 100px">미션 특성</th>
            {{--<th style="width: auto">제품 등록 정보</th>
            <th style="width: auto">장소 등록 정보</th>--}}
            <th style="width: 200px">제작자</th>
            <th style="width: 150px">제작일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($missions->groupBy('id') as $mission)
            <tr>
                <td style="text-align: center">{{ $mission[0]->id }}</td>
                <td style="text-align: center">{{ $mission[0]->category }}</td>
                <td><img src="{{ $mission[0]->thumbnail_image }}" alt="" width="100%"></td>
                <td>{{ $mission[0]->title }}</td>
                <td>{!! preg_replace('/(\r|\n|\r\n)/', '<br>', $mission[0]->description) !!}</td>
                <td>
                    @foreach($mission->pluck('area') as $area)
                        {{ $area }} {!! $loop->last ? '' : '<br>' !!}
                    @endforeach
                </td>
                <td style="text-align: center">{{ $mission[0]->success_count ? '1회성' : '일반' }}</td>
                <td>{{ $mission[0]->nickname }}<br>({{ $mission[0]->email }})</td>
                <td style="text-align: center">{{ $mission[0]->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center">미션이 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div style="text-align: center">{{ $missions->withQUeryString()->links() }}</div>
@endsection
