<?php
require_once 'inc/init.php';
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'], $_POST['user'], $_POST['pass'])) {
    $testUrl = rtrim($base, '/') . '/';
    $res = webdav_list($testUrl, $user, $pass);

    if ($res['status'] === 401 || $res['status'] === 403) {
        session_unset();
        header("Location: index.php?error=" . urlencode("Login failed – username or password incorrect."));
        exit;
    }

    $_SESSION['url']  = $base;
    $_SESSION['user'] = $user;
    $_SESSION['pass'] = $pass;
}

require_once 'inc/router.php';