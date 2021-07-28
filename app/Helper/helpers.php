<?php

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
