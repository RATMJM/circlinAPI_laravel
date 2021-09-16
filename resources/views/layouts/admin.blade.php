<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('css/admin/common.css?202109161600') }}">

    <title>써클인 - @yield('title')</title>
</head>
<body>
<div id="header">
    <div class="container">
        <div>
            <ul id="nav">
                <li><a href="{{ route('admin.user.index') }}">유저 통계</a></li>
                <li><a href="{{ route('admin.order.index') }}">주문 통계</a></li>
                <li><a href="{{ route('admin.mission.index') }}">미션 통계</a></li>
            </ul>
        </div>

        <p style="float: right">
            @auth
                <a href="{{ route('admin.logout') }}">로그아웃</a>
            @else
                <a href="{{ route('admin.login') }}">로그인</a>
            @endauth
        </p>
    </div>
</div>
<div class="container">
    @yield('content')
</div>
<div id="footer">

</div>
</body>
</html>
