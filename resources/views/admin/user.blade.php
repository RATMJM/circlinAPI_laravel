@extends('layouts.admin')

@section('title', '로그인')

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
    <a href="{{ route('admin.user', Arr::collapse([request()->all(), ['filter' => 'all']])) }}" class="btn">전체</a>
    <a href="{{ route('admin.user', Arr::collapse([request()->all(), ['filter' => 'day']])) }}" class="btn">금일</a>
    <a href="{{ route('admin.user', Arr::collapse([request()->all(), ['filter' => 'week']])) }}" class="btn">금주</a>
    <a href="{{ route('admin.user', Arr::collapse([request()->all(), ['filter' => 'month']])) }}" class="btn">금월</a>
    <br>
    <br>
    <br>
    <table>
        <thead>
        <tr>
            <th style="width: 100px">ID</th>
            <th style="width: auto">이메일</th>
            <th style="width: 200px">닉네임</th>
            <th style="width: 50px">성별</th>
            <th style="width: 200px">사는 동네</th>
            <th style="width: 80px">팔로잉</th>
            <th style="width: 150px">가입일</th>
        </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td style="text-align: center">{{ $user->id }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->nickname }}</td>
                <td style="text-align: center">{{ $user->gender }}</td>
                <td>{{ $user->area }}</td>
                <td style="text-align: center">{{ $user->following }}</td>
                <td style="text-align: center">{{ $user->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center">유저가 없습니다.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="text-align: center">{{ $users->withQUeryString()->links() }}</div>
@endsection
