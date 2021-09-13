@extends('layouts.admin')

@section('title', '로그인')

@section('content')
    <form action="{{ route('admin.login') }}" method="post">
        @csrf
        <label for="email">이메일</label><br><input type="text" name="email" id="email" autofocus><br>
        <label for="password">비밀번호</label><br><input type="password" name="password" id="password"><br>
        <button>로그인</button>
    </form>
@endsection
