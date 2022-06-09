@extends('layouts.admin')

@section('title', '배너 관리')

@section('content')
    <form method="POST" action="{{ route('admin.banner.store') }}" enctype="multipart/form-data" class="grid">
        @csrf
        <div class="item" style="grid-column: 1/-1; justify-content: flex-end">
            <button class="btn bg-blue">추가</button>
        </div>
        <div class="item" style="grid-column: 1/-1">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="item head"><label for="type">* 배너 위치</label></div>
        <div class="item">
            <select name="type" id="type" required>
                <option value="" disabled selected>배너 위치 선택</option>
                <option value="float" {{ old('type') === 'float' ? 'selected' : '' }}>메인 팝업</option>
                <option value="local" {{ old('type') === 'local' ? 'selected' : '' }}>미션 탭</option>
                <option value="shop" {{ old('type') === 'shop' ? 'selected' : '' }}>샵</option>
            </select>
        </div>
        <div class="item head"><label for="name">* 제목</label></div>
        <div class="item" style="grid-column: 2/-1">
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        </div>
        <div class="item head"><label for="description">설명</label></div>
        <div class="item" style="grid-column: 2/-1">
            <textarea type="text" name="description" id="description" rows="10">{{ old('description') }}</textarea>
        </div>
        <div class="item head"><label for="started_at">배너 기간</label></div>
        <div class="item">
            <input type="datetime-local" name="started_at" id="started_at"
                   value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}">
            ~
            <input type="datetime-local" name="ended_at" id="ended_at" value="{{ old('ended_at') }}">
        </div>
        <div class="item head"><label for="link_type">배너 연결 링크</label></div>
        <div class="item">
            <select name="link_type" id="link_type" onchange="linkTypeChange(this)" style="flex: 1">
                <option value="event_mission" {{ old('link_type') === 'event_mission' ? 'selected' : '' }}>이벤트 미션</option>
                {{--<option value="product">제품</option>--}}
                <option value="notice" {{ old('link_type') === 'notice' ? 'selected' : '' }}>공지사항</option>
                <option value="url" {{ old('link_type') === 'url' ? 'selected' : '' }}>외부 링크</option>
            </select>
            <div style="flex: 4">
                <select name="mission_id" id="mission_id">
                    <option value="" disabled selected>미션 선택</option>
                    @foreach($missions as $mission)
                        <option value="{{ $mission->id }}" {{ old('mission_id') === $mission->id ? 'selected' : '' }}>
                            [{{ $mission->owner?->nickname }}]
                            {{ $mission->title }}
                            ({{ explode(' ', $mission->started_at)[0] }} ~ {{ explode(' ', $mission->ended_at)[0] }})
                        </option>
                    @endforeach
                </select>
                <select name="notice_id" id="notice_id">
                    <option value="" disabled selected>공지사항 선택</option>
                    @foreach($notices as $notice)
                        <option value="{{ $notice->id }}" {{ old('notice_id') === $notice->id ? 'selected' : '' }}>
                            {{ $notice->title }}
                            ({{ explode(' ', $notice->created_at)[0] }})
                        </option>
                    @endforeach
                </select>
                <input type="text" name="link_url" id="link_url" value="{{ old('url') }}" placeholder="URL 입력">
            </div>
        </div>
        <div class="item head">* 배너 이미지</div>
        <div class="item">
            <input type="file" name="image" id="image" accept="image/*" style="flex: 1" oninput="setPreview(this)"
                   required>
            <div class="flex-center" style="flex: 1; height: 500px; background-color: #f6f6f6">
                <img src="" alt="" id="preview">
            </div>
        </div>
    </form>

    <style>
        input, textarea, select {
            width: 100%;
            resize: none;
        }

        input[type=file] {
            border: 1px solid #ddd;
        }

        .grid {
            display: grid;
            width: 1200px;
            grid-template-columns: 200px 1fr;
            gap: 16px;
            align-items: center;
        }

        .item {
            display: flex;
            flex-direction: row;
            gap: 8px;
            align-items: center;
        }
    </style>

    <script defer>
        const type = document.querySelector('#link_type');

        const mission = document.querySelector('#mission_id');
        const notice = document.querySelector('#notice_id');
        const url = document.querySelector('#link_url');

        function linkTypeChange(el) {
            mission.style.display = 'none';
            notice.style.display = 'none';
            url.style.display = 'none';

            if (el.value === 'event_mission') mission.style.display = null;
            else if (el.value === 'notice') notice.style.display = null;
            else if (el.value === 'url') url.style.display = null;
        }

        linkTypeChange(type);

        function setPreview(el) {
            const img = document.querySelector('#preview');

            if (el.files && el.files[0]) {
                img.onload = () => URL.revokeObjectURL(img.src);
                img.src = URL.createObjectURL(el.files[0]);
            } else {
                img.src = '';
            }
        }
    </script>
@endsection
