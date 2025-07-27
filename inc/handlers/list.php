<?php
if ($action === 'list' && substr($path ?? '', -1) !== '/') {
    header("Location: webdav.php?action=list&path=" . urlencode(($path ?? '') . '/'));
    exit;
}

$escapedBase = htmlspecialchars($base, ENT_QUOTES);
$escapedCurrentPath = htmlspecialchars($path ?? '/', ENT_QUOTES);
$encodedParentPath = urlencode(dirname(rtrim($path ?? '/', '/')) ?: '/');
$currentPath = $path ?? '/';

$fullUrl = rtrim($base, '/') . '/' . ltrim($currentPath, '/');

$res = webdav_list($fullUrl, $user, $pass);

if ($res['status'] === 401 || $res['status'] === 403) {
    if (!headers_sent()) {
        session_unset();
        header("Location: index.php?error=" . urlencode("Login failed."));
    } else {
        echo "<script>window.location.href='index.php?error=" . urlencode("Login failed.") . "';</script>";
    }
    exit;
}

if ($res['status'] !== 207) {
    session_unset();
    header("Location: index.php?error=" . urlencode("Invalid server or login."));
    exit;
}

$dom = new DOMDocument();
$dom->loadXML($res['body']);
$xpath = new DOMXPath($dom);
$xpath->registerNamespace('d', 'DAV:');
$nodes = $xpath->query('//d:response');

$visibleNodes = 0;
foreach ($nodes as $node) {
    $hrefNode = $xpath->query('d:href', $node)->item(0);
    if (!$hrefNode) continue;

    $href = urldecode($hrefNode->nodeValue);
    $hrefNormalized = rtrim($href, '/');
    $currentPathNormalized = rtrim($currentPath, '/');
    $isCurrentFolder = ($hrefNormalized === $currentPathNormalized);

    if (!$isCurrentFolder) {
        $visibleNodes++;
    }
}

$isCurrentPath = ($currentPath === '');

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>WebDAV Online Client</title>
	<link rel="stylesheet" href="css/style.css?v=14">
</head>
<body>
<h2>WebDAV Online Client</h2>
<div class="connection-info">
	<span>🔗 Connected to: <?php echo $escapedBase; ?></span>
</div>
<div class="nav-bar">
	<span class="current-path">📁 Folder: <?php echo $escapedCurrentPath; ?></span>
</div>
	<form class="back-form" action="webdav.php?action=list&path=<?php echo $encodedParentPath; ?>" method="post" style="display:inline-block;">
    	<button type="submit">🔙 Back</button>
	</form>
	<form class="logout-form" action="logout.php" method="post" style="display:inline-block;">
    	<button type="submit">🔓 Logout</button>
	</form>
	<form class="mkdir-form" action="webdav.php" method="post" style="display:inline-block;">
		<input type="hidden" name="action" value="mkdir">
		<input type="hidden" name="path" value="<?php echo $escapedCurrentPath; ?>">
		<input type="text" name="foldername" placeholder="New Foldername" required>
		<button type="submit">📁 Create folder</button>
	</form>
	<br><br>
	<div class="multi-form" style="display:inline-block;">
		<button type="submit" form="multi-action-form" formaction="webdav.php?action=zip" onclick="return startZip(this);">📦 Create ZIP from selection</button>
		<button type="submit" form="multi-action-form" formaction="webdav.php?action=move" onclick="return movePromptMulti(this);">🔀 Move selected</button>
		<button type="submit" form="multi-action-form" formaction="webdav.php?action=copy" onclick="return copyPromptMulti(this);">📄 Copy selected</button>
		<button type="submit" form="multi-action-form" formaction="webdav.php?action=delete" onclick="return deletePromptMulti(this);">🗑 Delete selected</button>
		<button type="button" form="multi-action-form" formaction="webdav.php?action=zip" onclick="startZipDownload(this);">📥 Download selected</button>
	</div>
  	<form id="multi-action-form" method="post">
  		<input type="hidden" name="path" value="<?= htmlspecialchars($currentPath) ?>">
  		<input type="hidden" name="current_dir" value="<?= htmlspecialchars($_GET['path'] ?? '/') ?>">
<div class="dav-list-container">
<table class="dav-list" cellspacing="0" cellpadding="0">
<thead>
	<tr>
		<th class="col-select" style="min-width: 20px;"><?php if (!$isCurrentPath && $visibleNodes > 0): ?><input type="checkbox" id="select-all"><?php endif; ?></th>
		<th class="col-name" style="width: 30%;">Name</th>
		<th class="col-type" style="width: 10%;">Type</th>
		<th class="col-size" style="width: 10%;">Size</th>
		<th class="col-modified" style="width: 30%;">Modified</th>
		<th class="col-actions" style="width: 20%;">Actions</th>
    </tr>
</thead>
<tbody>
	<?php
	$itemsShown = 0;
	foreach ($nodes as $node) {
		$hrefNode = $xpath->query('d:href', $node)->item(0);
		if (!$hrefNode) continue;
	
		$href = urldecode($hrefNode->nodeValue);
	
		$name = ($name = basename(rtrim($href, '/'))) !== '' ? $name : '[ROOT]';
		$hrefNormalized = rtrim($href, '/');
		$currentPathNormalized = rtrim($currentPath, '/');
		$isCurrentFolder = ($hrefNormalized === $currentPathNormalized);
	
		$typeNode = $xpath->query('d:propstat/d:prop/d:resourcetype', $node)->item(0);
		$sizeNode = $xpath->query('d:propstat/d:prop/d:getcontentlength', $node)->item(0);
		$modNode  = $xpath->query('d:propstat/d:prop/d:getlastmodified', $node)->item(0);
	
		$isDir = ($typeNode && $typeNode->getElementsByTagName('collection')->length > 0);
		$size = $isDir ? '-' : formatSize((int)($sizeNode ? $sizeNode->nodeValue : 0));
		$modified = $modNode ? $modNode->nodeValue : '';
		$icon = $isDir ? '📁' : '📄';
		$typeLabel = $isDir ? 'Folder' : 'File';
	
		if ($isDir && substr($href, -1) !== '/') {
			$href .= '/';
		}
	
		$encodedPath = implode('/', array_map('rawurlencode', explode('/', $href)));
		$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
		$canPreviewInline = in_array($extension, ['html', 'htm', 'php', 'txt', 'md', 'json', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
		
		if ($isDir) {
			$link = "<a class='folder-link' href='?action=list&path={$encodedPath}'>" . htmlspecialchars($name) . "</a>";
		} else {
			if ($canPreviewInline) {
				$previewUrl = "webdav.php?action=preview&path={$encodedPath}";
				$link = "<a href=\"$previewUrl\" target=\"_blank\">" . htmlspecialchars($name) . "</a>";
			} else {
				$link = "<a href=\"webdav.php?action=download&path={$encodedPath}\">" . htmlspecialchars($name) . "</a>";
			}
		}
	
$renameForm = <<<HTML
<form method="post" action="webdav.php?action=rename" style="display:inline;" onsubmit="return renamePrompt(this);">
	<input type="hidden" name="source" value="{$href}">
	<input type="hidden" name="target" value="">
	<button type="submit" class="action-link" title="Rename">✏️</button>
</form>
HTML;
	
$moveForm = <<<HTML
<form method="post" action="webdav.php?action=move" style="display:inline;" onsubmit="return movePrompt(this);">
	<input type="hidden" name="source" value="{$href}">
	<input type="hidden" name="target" value="">
	<button type="submit" class="action-link" title="Move">🔀</button>
</form>
HTML;

$copyForm = <<<HTML
<form method="post" action="webdav.php?action=copy" style="display:inline;" onsubmit="return copyPrompt(this);">
	<input type="hidden" name="source" value="{$href}">
	<input type="hidden" name="target" value="">
	<button type="submit" class="action-link" title="Copy">📄</button>
</form>
HTML;
	
$deleteForm = <<<HTML
<form method="post" action="webdav.php?action=delete" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this? {$typeLabel}: {$name}');">
	<input type="hidden" name="path" value="{$href}">
	<input type="hidden" name="single" value="1">
	<button type="submit" class="action-link" title="Delete">🗑</button>
</form>
HTML;
	
		if ($isDir) {
		$downloadLink = <<<HTML
			<form method="post" style="display:inline;" onsubmit="return startZipDownloadDirect(this);">
				<input type="hidden" name="path" value="{$currentPath}">
				<input type="hidden" name="selected[]" value="{$href}">
				<input type="hidden" name="zip_only" value="1">
				<button type="submit" class="action-link" title="Download Folder as ZIP">📥</button>
			</form>
		HTML;
		} else {
			$downloadLink = "<a class='action-link' href='webdav.php?action=download&path={$encodedPath}' title='Download File'>⬇️</a>";
		}
			
		$actions = $renameForm . $moveForm . $copyForm . $deleteForm . $downloadLink;
	?>
	<tr class="<?= $isCurrentFolder ? 'hidden-row' : '' ?>">
		<td class="col-select"><?php if (!$isCurrentFolder): ?><input type="checkbox" name="selected[]" value="<?= htmlspecialchars($href) ?>"><?php endif; ?></td>
		<td class="col-name"><?php echo $icon . ' ' . $link; ?></td>
		<td class="col-type"><?php echo $typeLabel; ?></td>
		<td class="col-size"><?php echo $size; ?></td>
		<td class="col-modified"><?php echo $modified; ?></td>
		<td class="col-actions"><?php echo $actions; ?></td>
	</tr>
		<?php
		$itemsShown++;
		}
		if ($itemsShown <= 1) {
			echo '<tr><td colspan="6" style="text-align:center; color:#999;">No files or folders found.</td></tr>';
		}
		?>
	</tbody>
	</table>
</div>
</form>
<div id="upload-container">
	<input type="hidden" id="uploadPath" value="<?= htmlspecialchars($currentPath ?? '/') ?>">
	<input type="file" id="fileInput" multiple webkitdirectory directory style="display:none;">
	<div id="dropzone" class="dropzone">📤 Drag & drop files here or click to upload</div>
</div>
<script src="js/spinner.js"></script>
<script src="js/loadlist.js"></script>
<script src="js/upload.js"></script>
<script src="js/rename.js"></script>
<script src="js/move.js"></script>
<script src="js/copy.js"></script>
<script src="js/delete.js"></script>
<script src="js/zip.js"></script>
<?php require_once 'inc/spinner.php'; ?>
<?php require_once 'inc/footer.php'; ?>
</body>
</html>
