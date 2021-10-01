@extends('layouts.admin')

@section('title', '공지사항 - '.$data->title)

@section('content')
    <form action="{{ route('admin.notice.update', ['notice' => $data->id]) }}" method="post">
        @csrf
        @method('PATCH')
        <label>
            <span>제목</span>
            <input name="title" value="{{ $data->title }}" style="width:100%">
        </label>
        <label>
            <span>내용</span>
            <textarea name="content" class="board" style="resize: none; width: 100%; height: 400px">{!! $data->content !!}</textarea>
        </label>
        <input type="radio" name="is_show" id="is_show_on" value="1" {{ $data->is_show ? 'checked' : '' }}><label for="is_show_on">노출 켜기</label>
        <input type="radio" name="is_show" id="is_show_off" value="0" {{ $data->is_show ? '' : 'checked' }}><label for="is_show_off">노출 끄기</label>
        <br><br>
        <button class="btn">수정</button>
    </form>
@endsection
