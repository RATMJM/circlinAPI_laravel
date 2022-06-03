@extends('layouts.admin')

@section('title', '미션 공지사항')

@section('content')
    <div class="grid">
        <div class="item img-wrapper" style="grid-row: 1/6; grid-column: 3/span 1">
            @foreach($data['images'] as $image)
                <img src="{{ $image->image }}" alt="">
            @endforeach
        </div>
        <div class="item head">제목</div>
        <div class="item">{{ $data['title'] }}</div>
        <div class="item" style="grid-column: 2/span 1">{{ $data['created_at'] }}</div>
        <div class="item head">내용</div>
        <div class="item">{{ $data['body'] }}</div>
        <div class="item" style="grid-column: 2/span 1; display: flex; gap: 8px">
            <a href="{{ route('admin.mission.notice.edit', ['mission_id' => $mission_id, 'notice' => $data['id']]) }}"
               class="btn">수정</a>
            <form
                action="{{ route('admin.mission.notice.destroy', ['mission_id' => $mission_id, 'notice' => $data['id']]) }}"
                method="POST" id="delete-form">
                @csrf
                @method('DELETE')
                <a href="javascript:if(confirm('정말로 삭제하시겠습니까?')) document.querySelector('#delete-form').submit()"
                   class="btn">삭제</a>
            </form>
        </div>
    </div>

    <style>
        .grid {
            display: grid;
            width: 1200px;
            grid-template-columns: 1fr 3fr 2fr;
        }

        .item {
            padding: 8px;
        }

        .img-wrapper {
            display: grid;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }
    </style>
@endsection
