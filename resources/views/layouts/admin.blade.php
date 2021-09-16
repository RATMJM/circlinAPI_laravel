<!doctype html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{ asset('css/admin/common.css?202109161300') }}">

    <title>써클인 - @yield('title')</title>
</head>
<body>
<div id="header">
    <div class="container">
        <p style="float: left">써클인</p>

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
