@extends('layouts.admin')

@section('title', '공지사항')

@section('content')
    <form action="{{ route('admin.order.index') }}">
        <input type="hidden" name="filter" value="{{ request()->get('filter') }}">
        <select name="type">
            <option value="all" selected>전체 (닉네임, 이메일)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요.">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($data->total()) }}</b> 명</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 160px">번호</th>
            <th style="width: 300px">이미지</th>
            <th style="width: auto">제목</th>
            <th style="width: 150px">작성일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td><img src="{{ $item->images[0]->image }}" alt="" width="100%"></td>
                <td><a href="{{ route('admin.notice.show', ['notice' => $item->id]) }}">{{ $item->title }}</a></td>
                <td class="center">{{ $item->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="0" class="center">주문이 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $data->withQUeryString()->links() }}</div>
@endsection
