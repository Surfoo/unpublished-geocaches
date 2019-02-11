<?php

/**
 * @param  array $data
 * 
 * @return string
 */
function renderAjax(array $data): string
{
    if (!headers_sent()) {
        header('Pragma: no-cache');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($data);
    exit(0);
}