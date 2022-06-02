@extends('layouts.admin')

@section('title', '미션')

@section('content')
    <div class="grid">
        <div class="item" style="grid-row: 1/span 5"><img src="{{ $data['thumbnail_image'] }}" alt=""></div>
        <div class="item head">제목</div>
        <div class="item">{{ $data['title'] }}</div>
        <div class="item head">내용</div>
        <div class="item">{{ $data['description'] }}</div>
        @if($data['is_event'])
            <div class="item center" style="grid-column: 2/span 2">
                <a href="{{ route('admin.mission.notice.index', ['mission_id' => $data['id']]) }}" class="btn">
                    공지사항으로
                </a>
            </div>
        @endif
    </div>
    <br>

    <style>
        .grid {
            display: grid;
            width: 1200px;
            grid-template-columns: 4fr 1fr 3fr;
        }

        .item {
            padding: 8px;
        }
    </style>
@endsection
