@extends('layouts.admin')

@section('title', '배너 관리')

@section('content')
    <div class="grid">
        <div class="item" style="grid-row: 1/span 10">
            <img src="{{ $data->image }}" alt="">
        </div>
        <div class="item head">ID</div>
        <div class="item">{{ $data->id }}</div>
        <div class="item head">정렬 번호</div>
        <div class="item">{{ $data->sort_num }}</div>
        <div class="item head">배너명</div>
        <div class="item">{{ $data->name }}</div>
        <div class="item head">배너 생성일</div>
        <div class="item">{{ $data->created_at }}</div>
        <div class="item head">배너 설명</div>
        <div class="item" style="grid-column: 3/-1">{{ $data->description }}</div>
        <div class="item head">배너 상태</div>
        <div class="item center">
            @if($data->is_available)
                <span class="itemor-red">노출 중</span>
            @else
                <span class="itemor-blue">종료</span>
            @endif
        </div>
        <div class="item head">배너 기간</div>
        <div class="item center">{{ $data->started_at ?? '-' }} ~ {{ $data->ended_at ?? '-' }}</div>
        <div class="item head">배너 링크</div>
        <div class="item flex-center" style="grid-column: 3/-1">
            @switch($data->link_type)
                @case('mission')
                @case('event_mission')
                    <a href="{{ route('admin.mission.show', ['mission_id' => $data->mission?->id ?? 0]) }}"
                       target="_blank" class="btn center bg-red">[미션] {{ $data->mission?->title ?? '' }}</a>
                    @break
                @case('feed')
                    <a href="{{ route('admin.feed.show', ['id' => $data->feed?->id ?? 0]) }}"
                       target="_blank" class="btn center bg-green">[피드]</a>
                    @break
                @case('product')
                    <a href="javascript:alert('제품 페이지가 아직 없습니다.')"
                       target="_blank" class="btn center bg-blue">[제품] {{ $data->product?->name_ko }}</a>
                    @break
                @case('url')
                    <a href="{{ $data->link_url }}" target="_blank" class="btn center bg-gray">
                        [URL] {{ $data->link_url }}
                    </a>
                    @break
            @endswitch
        </div>
        <div class="item head">배너 로그</div>
        <div class="item flex-center" style="grid-column: 3/-1">
            <a href="{{ route('admin.banner.log.show', ['id' => $data->id]) }}" class="btn center">로그 페이지로</a>
        </div>
        <br>
        <div class="item" style="grid-column: 2/-1; gap: 8px">
            <a href="{{ route('admin.banner.edit', ['id' => $data->id]) }}" class="btn">수정</a>
            <form action="{{ route('admin.banner.destroy', ['id' => $data->id]) }}" method="POST"
                onsubmit="return confirm('정말로 삭제하시겠습니까?')">
                @csrf
                @method('delete')
                <button href="" class="btn">삭제</button>
            </form>
        </div>
    </div>

    <style>
        .grid {
            display: grid;
            width: 1200px;
            grid-template-columns: 6fr 1fr 3fr 1fr 3fr;
            gap: 8px;
        }

        .item {
            display: flex;
            align-items: center;
        }
    </style>
@endsection
