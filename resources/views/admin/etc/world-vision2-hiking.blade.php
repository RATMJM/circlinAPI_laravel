@extends('layouts.admin')

@section('title', '피드 통계')

@section('content')
    <style>
        #table {
            border: 2px solid #000;
            border-right-width: 1px;
        }
        #table #head {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }
        #table p {
            padding: 5px 10px;
            border-right: 1px solid #000;
        }
        #table #head p:not(:last-child) {
            border-bottom: 1px solid #fff;
            border-right: 1px solid #fff;
        }
        #table > *:not(:last-child) p {
            border-bottom: 1px solid #000;
        }

        .flex-row {
            display: flex;
            flex-direction: row;
            width: 100%;
        }
        .flex-col {
            display: flex;
            flex-direction: column;
            width: 100%;
        }
    </style>
    <div id="table" class="flex-col">
        <div class="flex-row" id="head">
            <p class="flex-center" style="flex: 1">산 랭킹</p>
        </div>
        <div class="flex-row">
        @forelse($data as $i => $item)
            <div class="flex-col">
                <p class="flex-center" style="flex: 1">{{ $item->title }}</p>
                <p class="flex-center" style="flex: 1">{{ $item->feeds_count }}회 인증</p>
            </div>
        @empty
            <p class="flex-center" style="flex: 1">데이터가 없습니다.</p>
        @endforelse
        </div>
    </div>
    <br>
    <br>
    <br>
    <form action="{{ route('admin.world_vision2_hiking') }}">
        <select name="type">
            <option value="all" {{ $type === 'all' ? 'selected' : '' }}>전체 (닉네임, 이메일, 미션명)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($feeds->total()) }}</b> 개</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px">ID</th>
            <th style="width: 300px">이미지</th>
            <th style="width: auto">내용</th>
            <th style="width: 150px">산 이름</th>
            <th style="width: 300px">산 주소</th>
            <th style="width: 200px">작성자</th>
            <th style="width: 150px">작성일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($feeds as $feed)
            <tr>
                <td class="center">
                    {{ $feed->id }}
                    <br>{{ $feed->is_hidden ? '🔒︎' : '' }}
                </td>
                <td>
                    @foreach($feed->images as $image)
                        @if($image->type === 'image')
                            <img src="{{ $image->image }}" alt="" width="100%">
                        @else
                            <video src="{{ $image->image }}" width="100%" height="300px" controls></video>
                        @endif
                    @endforeach
                </td>
                <td>
                    {!! rn_to_br($feed->content) !!}
                </td>
                <td class="center">{{ $feed->title }}</td >
                <td>{{ $feed->address }}</td>
                <td>{{ $feed->nickname }}<br>({{ $feed->email }})</td>
                <td class="center">{{ $feed->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="0" class="center">피드가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $feeds->withQueryString()->links() }}</div>
@endsection
