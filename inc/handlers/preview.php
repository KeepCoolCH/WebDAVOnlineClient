<?php
require_once 'inc/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$user = $_SESSION['user'] ?? null;
$pass = $_SESSION['pass'] ?? null;

$path = $_GET['path'] ?? '';
if (!$user || !$pass || !$path) {
    http_response_code(403);
    exit("Access denied or missing path.");
}

$encodedPath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
$fullUrl = rtrim($base, '/') . '/' . $encodedPath;

$res = webdav_request('GET', $fullUrl, $user, $pass);

if ($res['status'] !== 200) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "WebDAV error: Status {$res['status']}\n";
    echo "URL: $fullUrl\n";
    echo "Path: $path\n";
    exit("Failed to load file.");
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$textExtensions = ['txt', 'php', 'html', 'htm', 'json', 'md', 'csv', 'log'];
$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (in_array($ext, $textExtensions)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo htmlspecialchars($res['body']);
    exit;
}

if (in_array($ext, $imageExtensions)) {
    $mimeMap = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    header('Content-Type: ' . $mimeMap[$ext]);
    echo $res['body'];
    exit;
}

header('Content-Type: application/octet-stream');
echo $res['body'];
exit;
