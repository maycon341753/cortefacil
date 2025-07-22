<?php
session_start();
if (!headers_sent()) {
    header('Content-Type: application/json');
}

echo json_encode([
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION ?? null,
    'cookies' => $_COOKIE ?? null
]);