<?php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$path = $_POST['path'] ?? $_GET['path'] ?? '/';

switch ($action) {
    case 'list':
        require 'inc/handlers/list.php';
        break;
    case 'mkdir':
        require 'inc/handlers/mkdir.php';
        break;
    case 'upload':
        require 'inc/handlers/upload.php';
        break;
    case 'download':
        require 'inc/handlers/download.php';
        break;
    case 'rename':
        require 'inc/handlers/rename.php';
        break;
    case 'move':
        require 'inc/handlers/move.php';
        break;
    case 'copy':
        require 'inc/handlers/copy.php';
        break;
    case 'zip':
        require 'inc/handlers/zip.php';
        break;
    case 'zipdownload':
        require 'inc/handlers/zip.php';
        break;
    case 'preview':
        require_once 'inc/handlers/preview.php';
        break;
    case 'delete':
        require 'inc/handlers/delete.php';
        break;
    default:
        echo json_encode(['error' => 'Unknown action']);
}
