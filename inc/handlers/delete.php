<?php
if (!$user || !$pass) {
    die("Access denied.");
}

$paths = [];

if (!empty($_POST['selected']) && is_array($_POST['selected'])) {
    $paths = $_POST['selected'];
}
elseif (!empty($_POST['path']) && isset($_POST['single'])) {
    $paths[] = $_POST['path'];
}

$errors = [];
foreach ($paths as $path) {
    $url = rtrim($base, '/') . '/' . str_replace('%2F', '/', rawurlencode(ltrim($path, '/')));

    $res = webdav_delete($url, $user, $pass);
    echo "DELETE $url → HTTP " . $res['status'] . "<br>";

    if ($res['status'] !== 204 && $res['status'] !== 200) {
        $errors[] = $path;
    }
}

$targetPath = $_POST['path'] ?? ($paths[0] ?? '/');
if (!empty($_POST['current_dir'])) {
    $redirectTo = $_POST['current_dir'];
} elseif (!empty($_POST['path'])) {
    $redirectTo = dirname(rtrim($_POST['path'], '/'));
    if ($redirectTo === '' || $redirectTo === '.') $redirectTo = '/';
} else {
    $redirectTo = '/';
}

if (!headers_sent()) {
    header("Location: webdav.php?action=list&path=" . urlencode($redirectTo));
    exit;
}