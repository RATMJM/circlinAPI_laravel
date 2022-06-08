@extends('layouts.admin')

@section('title', '배너 관리')

@section('content')
    <form method="POST" action="{{ route('admin.banner.update.all', ['type' => $type]) }}">
        @csrf
        @method('PUT')
        <div class="grid">
            <div class="left">
                <a href="{{ route('admin.banner.edit.all', Arr::collapse([request()->query(), ['type' => 'float']])) }}"
                   class="btn">
                    홈
                </a>
                <a href="{{ route('admin.banner.edit.all', Arr::collapse([request()->query(), ['type' => 'local']])) }}"
                   class="btn">
                    미션탭
                </a>
                <a href="{{ route('admin.banner.edit.all', Arr::collapse([request()->query(), ['type' => 'shop']])) }}"
                   class="btn">
                    샵
                </a>
            </div>
            <div class="right">
                <button class="btn">적용</button>
            </div>
        </div>
        <br>
        <div class="table">
            <div class="row head">
                <div class="col">배너</div>
                <div class="col">제목</div>
                <div class="col">기간</div>
                <div class="col">노출 순서<br>(높을 수록 우선)</div>
            </div>
        </div>
        <div class="table" id="table"></div>
    </form>
    <div id="dummy" style="display: none">
        <div class="row">
            <div class="col flex-center">
                <img src="" alt="" style="max-height: 180px">
            </div>
            <div class="col"></div>
            <div class="col center"></div>
            <div class="col center">
                <a class="btn up" data-id="" onclick="up(this, Number(this.getAttribute('data-id')))">
                    ▲
                </a>
                <input type="hidden" name="sort_num">
                <span>0</span>
                <a class="btn down" data-id="" onclick="down(this, Number(this.getAttribute('data-id')))">
                    ▼
                </a>
            </div>
        </div>
    </div>

    <style>
        .grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
        }

        .row {
            grid-template-columns: 300px 1fr 160px 130px;
        }
    </style>

    <script defer>
        let data = {!! json_encode($data, JSON_UNESCAPED_UNICODE) !!};

        const table = document.querySelector('#table');
        const dummy = document.querySelector('#dummy > .row');

        function render() {
            table.innerHTML = '';

            let i = data.length;
            data.forEach(function (item) {
                const row = dummy.cloneNode(true);

                row.childNodes[1].childNodes[1].src = item.image;
                row.childNodes[3].innerText = item.name;
                row.childNodes[5].innerHTML = `${item.started_at ?? '-'}<br>~<br>${item.ended_at ?? '-'}`;

                row.childNodes[7].childNodes[1].setAttribute('data-id', item.id);
                row.childNodes[7].childNodes[7].setAttribute('data-id', item.id);
                row.childNodes[7].childNodes[3].name = `sort_num[${item.id}]`;
                row.childNodes[7].childNodes[3].value = i--;
                row.childNodes[7].childNodes[5].innerText = item.sort_num;

                table.appendChild(row);
            });

            console.log(data)
        }

        function up(el, id) {
            // 맨 위 차단
            if ((i = data.findIndex((item) => item.id === id)) === 0) return false;

            // 위로
            const slice = data.splice(i, 1)[0];
            data.splice(i - 1, 0, slice);

            render();
        }

        function down(el, id) {
            // 맨 아래 차단
            if ((i = data.findIndex((item) => item.id === id)) >= data.length - 1) return false;

            // 아래로
            const slice = data.splice(i, 1)[0];
            data.splice(i + 1, 0, slice);

            render();
        }

        render();
    </script>
@endsection
