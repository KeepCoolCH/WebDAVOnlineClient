<?php
$tempDir = __DIR__ . '/../.temp/';

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "Missing file parameter.";
    exit;
}

$filename = basename($_GET['file']);
$filepath = $tempDir . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'zip') {
    http_response_code(403);
    echo "Invalid file type.";
    exit;
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);

unlink($filepath);
exit;
