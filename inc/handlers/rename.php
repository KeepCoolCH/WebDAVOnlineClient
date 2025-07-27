<?php
$source = $_POST['source'] ?? '';
$target = $_POST['target'] ?? '';

if (!$source || !$target) {
    echo "Source or target name is missing.";
    exit;
}

$sourceUrl = rtrim($base, '/') . '/' . ltrim($source, '/');
$targetUrl = rtrim($base, '/') . '/' . ltrim($target, '/');

$res = webdav_move($sourceUrl, $targetUrl, $user, $pass);

$backTo = dirname($source);
$backTo = ($backTo === '' || $backTo === '.' || $backTo === '/') ? '/' : rtrim($backTo, '/') . '/';

header("Location: webdav.php?action=list&path=" . urlencode($backTo));
exit;
