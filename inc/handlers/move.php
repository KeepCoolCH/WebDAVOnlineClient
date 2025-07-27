<?php
if (!$user || !$pass) {
    die("Access denied.");
}

$targetDir = $_POST['target'] ?? '';
if (!$targetDir || trim($targetDir) === '') {
    $targetDir = $_POST['current_dir'] ?? '/';
}

$sources = [];

if (!empty($_POST['selected']) && is_array($_POST['selected'])) {
    $sources = $_POST['selected'];
} elseif (!empty($_POST['source'])) {
    $sources[] = $_POST['source'];
}

if (empty($sources)) {
    die("No source files or folders specified.");
}

function build_webdav_url($base, $path) {
    $segments = explode('/', ltrim($path, '/'));
    $encoded = array_map('rawurlencode', $segments);
    return rtrim($base, '/') . '/' . implode('/', $encoded);
}

$errors = [];

foreach ($sources as $source) {
    $filename  = basename($source);
    $sourceUrl = build_webdav_url($base, $source);
    $targetUrl = build_webdav_url($base, rtrim($targetDir, '/') . '/' . $filename);

    $res = webdav_move($sourceUrl, $targetUrl, $user, $pass);

    if ($res['status'] !== 201 && $res['status'] !== 204) {
        $errors[] = $source;
    }
}

if (!empty($_POST['current_dir'])) {
    $redirectTo = $_POST['current_dir'];
} elseif (!empty($_POST['source'])) {
    $redirectTo = dirname($_POST['source']);
} elseif (!empty($sources[0])) {
    $redirectTo = dirname($sources[0]);
} else {
    $redirectTo = '/';
}

$redirectTo = ($redirectTo === '' || $redirectTo === '.' || $redirectTo === '/') ? '/' : $redirectTo;

header("Location: webdav.php?action=list&path=" . urlencode($redirectTo));
exit;
