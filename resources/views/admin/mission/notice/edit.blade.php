@extends('layouts.admin')

@section('title', '공지사항 관리 - '.$data->title)

@section('content')
    <form action="{{ route('admin.mission.notice.update', ['mission_id' => $mission_id, 'notice' => $data['id']]) }}"
          method="POST" class="grid">
        @csrf
        @method('PUT')
        <div class="item img-wrapper" style="grid-row: 1/6; grid-column: 3/span 1">
            @foreach($data['images'] as $image)
                <img src="{{ $image->image }}" alt="">
            @endforeach
        </div>
        <div class="item head"><label for="title">제목</label></div>
        <div class="item"><input type="text" name="title" id="title" value="{{ $data['title'] }}"></div>
        <div class="item" style="grid-column: 2/span 1">{{ $data['created_at'] }}</div>
        <div class="item head"><label for="body">내용</label></div>
        <div class="item"><textarea name="body" id="body" rows="20">{{ $data['body'] }}</textarea></div>
        <div class="item" style="grid-column: 2/span 1; display: flex; gap: 8px">
            <button class="btn">수정</button>
        </div>
    </form>

    <style>
        .grid {
            display: grid;
            width: 1200px;
            grid-template-columns: 1fr 3fr 2fr;
        }

        .item {
            display: flex;
            padding: 8px;
        }

        .img-wrapper {
            display: grid;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        input, textarea {
            width: 100%;
            resize: none;
        }
    </style>
@endsection
