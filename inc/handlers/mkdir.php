<?php
$newFolder = $_POST['foldername'] ?? '';
$targetPath = $_POST['path'] ?? '/';

if ($newFolder === '') {
    echo "Folder name is missing.";
    exit;
}

$mkdirUrl = rtrim($base, '/') . '/' . ltrim($targetPath, '/') . '/' . rawurlencode($newFolder) . '/';
$res = webdav_mkdir($mkdirUrl, $user, $pass);

$backTo = rtrim($targetPath, '/') . '/';
header("Location: webdav.php?action=list&path=" . urlencode($backTo));
exit;
