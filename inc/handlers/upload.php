<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../functions.php';

$tempDir = __DIR__ . '/../.temp/';

$targetPath = $_POST['basepath'] ?? '/';
$count = (int)($_POST['count'] ?? 0);
$uploaded = 0;
$uploadId = 'upload_' . uniqid();
$uploadPath = $tempDir . $uploadId;
mkdir($uploadPath, 0775, true);

$structureFile = $uploadPath . '/structure.txt';
$structure = [];

for ($i = 0; $i < $count; $i++) {
    $fileKey = "file_$i";
    $pathKey = "path_$i";

    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        continue;
    }

    $originalName = $_FILES[$fileKey]['name'];
    $tmpName = $_FILES[$fileKey]['tmp_name'];
    $relativePath = ltrim($_POST[$pathKey] ?? $originalName, '/');
    $targetFile = $uploadPath . '/' . $relativePath;

    $targetDir = dirname($targetFile);
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($tmpName, $targetFile)) {
        $structure[] = $relativePath;
        $uploaded++;
    }
}

file_put_contents($structureFile, implode("\n", $structure));

if ($uploaded > 0) {
    foreach ($structure as $relPath) {
        $localFile = $uploadPath . '/' . $relPath;
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $relPath)));
        $uploadUrl = rtrim($base, '/') . '/' . ltrim($targetPath, '/') . '/' . $encodedPath;

        $dirParts = explode('/', $relPath);
        array_pop($dirParts);
        $subDir = '';
        foreach ($dirParts as $part) {
            $subDir .= '/' . $part;
            $url = rtrim($base, '/') . '/' . ltrim($targetPath, '/') . $subDir;
            webdav_mkdir($url, $user, $pass);
        }

        webdav_upload($uploadUrl, $user, $pass, $localFile);
    }
}

function deleteDir($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

if (strpos(realpath($uploadPath), realpath($tempDir)) === 0) {
    deleteDir($uploadPath);
}

$redirectPath = urlencode($targetPath);
echo "REDIRECT: webdav.php?action=list&path={$redirectPath}";
exit;