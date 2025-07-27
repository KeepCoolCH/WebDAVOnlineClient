<?php
$downloadDirect = ($_GET['action'] ?? $_POST['action'] ?? '') === 'zipdownload';
$selected = $_POST['selected'] ?? [];
$targetPath = $_POST['path'] ?? '/';
$zipName = 'archive_' . date('Ymd_His') . '.zip';
$tempDir = __DIR__ . '/../.temp/';
$tempZip = $tempDir . $zipName;

$tempFiles = [];

mb_internal_encoding("UTF-8");

function addFolderToZip($baseUrl, $folderPath, $zip, $user, $pass, $prefix = '', $tempDir = '', &$tempFiles = []) {
    $list = webdav_list(rtrim($baseUrl, '/') . '/' . ltrim($folderPath, '/'), $user, $pass);
    if ($list['status'] !== 207) return;

    $dom = new DOMDocument();
    @$dom->loadXML($list['body']);
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('d', 'DAV:');

    foreach ($xpath->query('//d:response') as $node) {
        $hrefNode = $xpath->query('d:href', $node)->item(0);
        if (!$hrefNode) continue;

        $href = urldecode($hrefNode->nodeValue);
        $isDir = $xpath->query('d:propstat/d:prop/d:resourcetype/d:collection', $node)->length > 0;

        if (parse_url($href, PHP_URL_PATH) === parse_url($folderPath, PHP_URL_PATH)) continue;

        $relative = ltrim(str_replace($folderPath, '', $href), '/');
        $relative = str_replace(['\\', '//'], '/', $relative);

        if ($isDir) {
            $zip->addEmptyDir($prefix . $relative);
            addFolderToZip($baseUrl, $href, $zip, $user, $pass, $prefix . $relative . '/', $tempDir, $tempFiles);
        } else {
            $encodedHref = implode('/', array_map('rawurlencode', explode('/', ltrim($href, '/'))));
			$fileUrl = rtrim($baseUrl, '/') . '/' . $encodedHref;
            $res = webdav_request('GET', $fileUrl, $user, $pass);
            if ($res['status'] === 200) {
                $tempFile = tempnam($tempDir, 'webdav_');
                file_put_contents($tempFile, $res['body']);

                $zipPath = $prefix . $relative;
                $zip->addFile($tempFile, $zipPath);
                $tempFiles[] = $tempFile;
            }
        }
    }
}

$zip = new ZipArchive();
if ($zip->open($tempZip, ZipArchive::CREATE) !== true) {
    die("❌ ZIP could not be created.");
}

foreach ($selected as $item) {
    $isDir = substr($item, -1) === '/';
    $cleanItem = rtrim($item, '/');
    $encodedItem = implode('/', array_map('rawurlencode', explode('/', ltrim($item, '/'))));
	$sourceUrl = rtrim($base, '/') . '/' . $encodedItem;

    if ($isDir) {
        $folderName = basename($cleanItem);
        $zip->addEmptyDir($folderName);
        addFolderToZip($base, $item, $zip, $user, $pass, $folderName . '/', $tempDir, $tempFiles);
    } else {
        $res = webdav_request('GET', $sourceUrl, $user, $pass);
        if ($res['status'] === 200) {
            $tempFile = tempnam($tempDir, 'webdav_');
            file_put_contents($tempFile, $res['body']);

            $fileName = basename($item);
            $zip->addFile($tempFile, $fileName);
            $tempFiles[] = $tempFile;
        }
    }
}

$zip->close();

foreach ($tempFiles as $tmp) {
    unlink($tmp);
}

if ($downloadDirect) {
    if (isset($_POST['zip_only'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'zipUrl' => 'inc/handlers/zipdownload.php?file=' . rawurlencode(basename($tempZip))
        ]);
        exit;
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($tempZip));
    readfile($tempZip);
    unlink($tempZip);
    exit;
} else {
    $uploadUrl = rtrim($base, '/') . '/' . ltrim($targetPath, '/') . '/' . $zipName;
    webdav_upload($uploadUrl, $user, $pass, $tempZip);
    unlink($tempZip);
    header("Location: webdav.php?action=list&path=" . urlencode($targetPath));
    exit;
}
