@extends('layouts.admin')

@section('title', '공지사항 관리 - '.$data->title)

@section('content')
    <div>
        <h1>{{ $data->title }}</h1>
        <div class="board">{!! rn_to_br($data->content) !!}</div>
        <div>
            @foreach($data->images as $image)
                <img src="{{ $image->image }}" alt="" width="300px">
            @endforeach
        </div>
    </div>

    <div>
        <a href="{{ route('admin.notice.index') }}" class="btn">목록</a>
        @if($data->is_show)
            <a href="javascript:update_show(0)" class="btn">노출 끄기</a>
        @else
            <a href="javascript:update_show(1)" class="btn">노출 켜기</a>
        @endif
        <a href="{{ route('admin.notice.edit', ['notice' => $data->id]) }}" class="btn">수정</a>
        <a href="javascript:destroy()" class="btn">삭제</a>
    </div>

    <form action="{{ route('admin.notice.update_show', ['notice' => $data->id]) }}" method="post" id="update_show_form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="is_show">
    </form>
    <form action="{{ route('admin.notice.destroy', ['notice' => $data->id]) }}" method="post" id="destroy_form">
        @csrf
        @method('DELETE')
    </form>
    <script>
        function update_show(bool) {
            document.querySelector("input[name=is_show]").value = bool;
            document.querySelector("#update_show_form").submit();
        }

        function destroy() {
            if (prompt('삭제하시려면 "삭제" 를 입력해주세요') === '삭제') {
                document.querySelector("#destroy_form").submit();
            }
        }
    </script>
@endsection
