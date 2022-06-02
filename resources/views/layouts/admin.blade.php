<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('css/admin/common.css?202206021938') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/board.css?202110081200') }}">

    <title>써클인 어드민 - @yield('title')</title>
</head>
<body>
<div id="header">
    <div class="container">
        <ul id="nav">
            <li><a href="{{ route('admin.user.index') }}">유저 통계</a></li>
            <li><a href="{{ route('admin.order.index') }}">주문 통계</a></li>
            <li><a href="{{ route('admin.mission.index') }}">미션 통계</a></li>
            <li><a href="{{ route('admin.feed.index') }}">피드 통계</a></li>
            <li><a href="{{ route('admin.banner.log.index') }}">배너 클릭률 통계</a></li>
            <li><a href="{{ route('admin.notice.index') }}">공지사항 관리</a></li>
            <li><a href="{{ route('admin.push.index') }}">푸시 관리</a></li>
        </ul>
        <div style="padding: 10px">
            @auth
                <a href="{{ route('admin.logout') }}">로그아웃</a>
                <br>
                <span>{{ auth()->user()->nickname ?? '' }} ({{ auth()->user()->email ?? '' }}) 님 안녕하세요!</span>
            @else
                <a href="{{ route('admin.login') }}">로그인</a>
            @endauth
        </div>
    </div>
</div>
<div class="container">
    @yield('content')
</div>
<div id="footer">

</div>
</body>
</html>
