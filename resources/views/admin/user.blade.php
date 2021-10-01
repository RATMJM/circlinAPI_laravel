@extends('layouts.admin')

@section('title', '유저 통계')

@section('content')
    <table>
        <thead>
        <tr>
            <th>전체 회원 수</th>
            <th>금일 가입자 수</th>
            <th>금주 가입자 수</th>
            <th>금월 가입자 수</th>
            <th>5.5 이전 탈퇴자 수</th>
            <th>5.5 이후 탈퇴자 수</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ number_format($users_count['all']) }}</td>
            <td>{{ number_format($users_count['day']) }}</td>
            <td>{{ number_format($users_count['week']) }}</td>
            <td>{{ number_format($users_count['month']) }}</td>
            <td>{{ number_format($deleted_old_users_count) }}</td>
            <td>{{ number_format($deleted_users_count) }}</td>
        </tr>
        </tbody>
    </table>
    <br>
    <br>
    <br>
    <a href="{{ route('admin.user.index', Arr::collapse([request()->all(), ['filter' => 'all']])) }}" class="btn">전체</a>
    <a href="{{ route('admin.user.index', Arr::collapse([request()->all(), ['filter' => 'day']])) }}" class="btn">금일</a>
    <a href="{{ route('admin.user.index', Arr::collapse([request()->all(), ['filter' => 'week']])) }}" class="btn">금주</a>
    <a href="{{ route('admin.user.index', Arr::collapse([request()->all(), ['filter' => 'month']])) }}" class="btn">금월</a>
    <br>
    <br>
    <br>
    <form action="{{ route('admin.user.index') }}">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <select name="type">
            <option value="all" selected>전체 (닉네임, 이메일)</option>
        </select>
        <input name="keyword" type="text" placeholder="검색 내용을 입력해주세요." value="{{ $keyword }}">
        <button>검색</button>
    </form>
    <br>
    <p style="font-size: 20px">검색 결과 : <b>{{ number_format($users->total()) }}</b> 명</p>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px">ID</th>
            <th style="width: 100px">가입 경로</th>
            <th style="width: auto">이메일</th>
            <th style="width: 200px">닉네임</th>
            <th style="width: 50px">성별</th>
            <th style="width: 150px">생년월일</th>
            <th style="width: 220px">사는 동네</th>
            <th style="width: 80px">팔로잉</th>
            <th style="width: 150px">가입일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td class="center">{{ $user->id }}</td>
                <td>
                    @php(preg_match('/@(.+)$/', $user->email, $at) && $at = $at[1])
                    @if($at === 'K')
                        카카오
                    @elseif($at === 'N')
                        네이버
                    @elseif($at === 'F')
                        페이스북
                    @elseif($at === 'A')
                        애플
                    @else
                        이메일
                    @endif
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->nickname }}</td>
                <td class="center">
                    @if($user->gender === 'M')
                        <span style="color:blue">남</span>
                    @elseif($user->gender === 'W')
                        <span style="color:red">여</span>
                    @endif
                </td>
                <td class="center">@if($user->birthday){{ date('Y년 m월 d일', strtotime($user->birthday)) }}@endif</td>
                <td>{{ $user->area }}</td>
                <td class="center">{{ $user->following }}</td>
                <td class="center">{{ $user->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="0" class="center">유저가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <br>
    <div class="center">{{ $users->withQUeryString()->links() }}</div>
@endsection
