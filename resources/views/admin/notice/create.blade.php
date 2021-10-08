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
        function add_file() {
            let file_order = 0;
            let orders = document.querySelectorAll('input[name="orders[]"]');

            orders.forEach((item, i) => file_order = Math.max(file_order, Number(item.value) + 1));

            let remove_button = document.createElement('button');
            remove_button.type = 'button';
            remove_button.classList.add('btn');
            remove_button.innerHTML = '-';
            remove_button.onclick = remove_file;

            let order = document.createElement('input');
            order.type = 'number';
            order.name = 'orders[]';
            order.value = file_order;
            order.style = "width: 40px";

            let file = document.createElement('input');
            file.type = 'file';
            file.name = 'files[]';

            let div = document.createElement('div');
            div.append(remove_button);
            div.append(' ');
            div.append(order);
            div.append(file);
            document.querySelector('#files').append(div);
        }

        function remove_file() {
            this.parentElement.remove();
        }

        add_file();
    </script>
@endsection
