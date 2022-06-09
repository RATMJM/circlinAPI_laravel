@extends('layouts.admin')

@section('title', '배너 관리')

@section('content')
    <a href="{{ route('admin.banner.index', Arr::collapse([request()->query(), ['type' => 'float']])) }}" class="btn">
        홈
    </a>
    <a href="{{ route('admin.banner.index', Arr::collapse([request()->query(), ['type' => 'local']])) }}" class="btn">
        미션탭
    </a>
    <a href="{{ route('admin.banner.index', Arr::collapse([request()->query(), ['type' => 'shop']])) }}" class="btn">
        샵
    </a>
    <br><br>
    <a href="{{ route('admin.banner.edit.all', Arr::collapse([request()->query(), ['type' => $type]])) }}" class="btn">
        배너 정렬 수정
    </a>
    <a href="{{ route('admin.banner.create') }}" class="btn" style="float: right">작성</a>
    <br><br>
    <div class="table">
        <div class="row head">
            <div class="col">ID</div>
            <div class="col">노출 순서<br>(높을 수록 우선)</div>
            <div class="col">이미지</div>
            <div class="col">제목</div>
            <div class="col">상태</div>
            <div class="col">배너 기간</div>
            <div class="col">링크</div>
        </div>
        @foreach($data as $item)
            <div class="row">
                <div class="col">{{ $item->id }}</div>
                <div class="col center">{{ $item->sort_num }}</div>
                <a href="{{ route('admin.banner.show', ['type' => $type, 'id' => $item->id]) }}" class="col flex-center">
                    <img src="{{ $item->image }}" alt="" style="max-height: 180px">
                </a>
                <a href="{{ route('admin.banner.show', ['type' => $type, 'id' => $item->id]) }}" class="col"
                   style="grid-template-rows: 1fr 2fr">
                    <b>{{ $item->name }}</b>
                    <span style="padding-left: 16px; height:100%">{{ $item->description }}</span>
                </a>
                <div class="col center">
                    @if($item->is_available)
                        <span class="color-red">노출 중</span>
                    @else
                        <span class="color-blue">종료</span>
                    @endif
                </div>
                <div class="col center">{{ $item->started_at ?? '-' }}<br>~<br>{{ $item->ended_at ?? '-' }}</div>
                <div class="col">
                    @switch($item->link_type)
                        @case('mission')
                        @case('event_mission')
                            <a href="{{ route('admin.mission.show', ['mission_id' => $item->mission?->id ?? 0]) }}"
                               target="_blank" class="btn center bg-red">[미션]<br>{{ $item->mission?->title ?? '' }}</a>
                            @break
                        @case('feed')
                            <a href="{{ route('admin.feed.show', ['id' => $item->feed?->id ?? 0]) }}"
                               target="_blank" class="btn center bg-green">[피드]</a>
                            @break
                        @case('product')
                            <a href="javascript:alert('제품 페이지가 아직 없습니다.')"
                               target="_blank" class="btn center bg-blue">[제품]<br>{{ $item->product?->name_ko }}</a>
                            @break
                        @case('url')
                            <a href="{{ $item->link_url }}" target="_blank" class="btn center bg-gray">
                                [URL]<br>{{ $item->link_url }}
                            </a>
                            @break
                    @endswitch
                </div>
            </div>
        @endforeach
    </div>
    <br>
    <div class="center">{{ $data->withQueryString()->links() }}</div>

    <style>
        .row {
            grid-template-columns: 60px 130px 300px 1fr 60px 160px 300px;
        }
    </style>
@endsection
