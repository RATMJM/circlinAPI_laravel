@extends('layouts.admin')

@section('title', '공지사항 - 작성하기')

@section('content')
    <form action="{{ route('admin.notice.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label>
            <span>제목</span>
            <input name="title" style="width:100%">
        </label>
        <label>
            <span>내용</span>
            <textarea name="content" class="board" style="resize: none; width: 100%; height: 400px"></textarea>
        </label>
        <br>
        <div>
            <button type="button" class="btn" onclick="add_file()">+</button>
            <div id="files"></div>
        </div>
        <br>
        <div>
            <input type="radio" name="is_show" id="is_show_on" value="1" checked><label for="is_show_on">노출 켜기</label>
            <input type="radio" name="is_show" id="is_show_off" value="0"><label for="is_show_off">노출 끄기</label>
        </div>
        <br>
        <button class="btn">게시</button>
    </form>

    <script>
        let file_count = 0;

        function add_file() {
            let remove_button = document.createElement('button');
            remove_button.type = 'button';
            remove_button.classList.add('btn');
            remove_button.innerHTML = '-';
            remove_button.onclick = remove_file;

            let file = document.createElement('input');
            file.type = 'file';
            file.name = 'files[]';
            file.id = 'file'+(++file_count);

            let div = document.createElement('div');
            div.append(remove_button);
            div.append(file);
            document.querySelector('#files').append(div);
        }

        function remove_file() {
            this.parentElement.remove();
        }

        add_file();
    </script>
@endsection
