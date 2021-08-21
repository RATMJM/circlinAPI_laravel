<h1>기본 세팅</h1>
<p>copy .env.example .env && composer update</p>
<p>php artisan key:generate</p>
<h1>윈도우 Imagick 설치</h1>
<p><a href="https://mlocati.github.io/articles/php-windows-imagick.html">Imagick</a></p>
<ol>
    <li>버전에 맞는 zip 다운로드</li>
    <li>php_imagick.dll - php/ext 폴더로</li>
    <li>CORE_RL, IM_MOD_RL 로 시작하는 파일들 php.exe 있는 폴더로</li>
    <li>php.ini 에 `extension=imagick` 추가</li>
    <li>httpd -k restart</li>
</ol>
