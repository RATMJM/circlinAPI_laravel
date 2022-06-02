@extends('layouts.admin')

@section('title', '미션 공지사항')

@section('content')
    <br>
    <div class="grid">
        <p style="font-size: 20px">검색 결과 : <b>{{ number_format($data->total()) }}</b> 개</p>
        <a href="{{ route('admin.mission.notice.create', ['mission_id' => $mission_id]) }}" class="btn center">작성하기</a>
    </div>
    <br>
    <div class="table">
        <div class="row head">
            <div class="col">ID</div>
            <div class="col" style="grid-column: 2/span 2">제목</div>
            <div class="col">작성일</div>
        </div>
        @foreach ($data as $item)
            <a class="row" href="{{ route('admin.mission.notice.show', ['mission_id' => $mission_id, 'notice' => $item->id]) }}">
                <div class="col">{{ $item->id }}</div>
                <div class="col"><img src="{{ $item->images[0]?->image ?? '' }}" alt=""></div>
                <div class="col">{{ $item->title }}</div>
                <div class="col center">{{ $item->created_at }}</div>
            </a>
        @endforeach
    </div>
    <br>
    <div class="center">{{ $data->withQueryString()->links() }}</div>

    <style>
        .grid {
            display: grid;
            grid-template-columns: 1fr 100px;
        }

        .row {
            grid-template-columns: 60px 300px 1fr 150px;
        }
    </style>
@endsection
