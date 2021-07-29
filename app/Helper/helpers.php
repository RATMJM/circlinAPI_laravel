<?php

const ALLOW_IP = ['::1', '127.0.0.1', '34.64.248.255', '34.85.2.191', '112.169.13.48'];

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
