@extends('layouts.admin')

@section('title', '미션 공지사항 작성')

@section('content')
    <form class="table" method="POST" action="{{ route('admin.mission.notice.store', ['mission_id' => $mission_id]) }}"
        enctype="multipart/form-data">
        @csrf
        <div class="grid">
            <div class="col head"><label for="title">제목</label></div>
            <div class="col"><input type="text" name="title" id="title"></div>
            <div class="col head"><label for="body">내용</label></div>
            <div class="col"><textarea name="body" id="body" cols="30" rows="10"></textarea></div>
            <div class="col head">이미지</div>
            <div class="col">
                <button type="button" class="btn" onclick="add_file()">+</button>
                <div id="files"></div>
            </div>
            <div class="col" style="grid-column: 2/-1">
                <div class="center">
                    <button class="btn" style="width:auto">게시</button>
                </div>
            </div>
        </div>
    </form>

    <style>
        .grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 8px;
        }

        .btn {
            width: 30px;
        }
    </style>

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
            file.accept = 'image/*';

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

        // add_file();
    </script>
@endsection
