<?php

use Firebase\JWT\JWT;

const ALLOW_IP = [
    // 테스트
    '::1', '127.0.0.1',
    // 서버
    '34.64.248.255', '34.85.2.191',
    // 사내
    '112.169.13.48',
    // 개인
    '124.5.120.66',
];

/* 결과 정상 전달 */
function success($data): array
{
    return [
        'success' => true,
        'data' => $data,
    ];
}

/* 도중 오류 발생 */
function failed(Exception $e): array
{
    return [
        'success' => false,
        'reason' => 'error',
        'message' => $e->getMessage(),
    ];
}

function token(): object
{
    try {
        $token = request()->header('token');

        if (is_null($token)) {
            abort(403);
        }

        return JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (Exception $e) {
        abort(403);
    }
}
