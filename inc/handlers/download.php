<?php
require_once __DIR__ . '/../functions.php';

$user = $_SESSION['user'] ?? '';
$pass = $_SESSION['pass'] ?? '';
$base = $_SESSION['url'] ?? '';
$path = $_GET['path'] ?? '';

if (!$user || !$pass || !$base || !$path) {
    http_response_code(403);
    echo "Session expired or invalid.";
    exit;
}

$downloadUrl = rtrim($base, '/') . '/' . ltrim($path, '/');

$res = webdav_request('GET', $downloadUrl, $user, $pass);

if ($res['status'] === 200) {
    $filename = basename(urldecode($path));

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($res['body']));

    echo $res['body'];
    exit;
}

http_response_code($res['status']);
echo "Download failed. HTTP status: " . $res['status'];
exit;