<?php
$tempDir = __DIR__ . '/.temp/';
	if (!is_dir($tempDir)) {
		mkdir($tempDir, 0775, true);
	}
	
	// Generate .htaccess for Upload-Folder (deny direct access)
	$htaccessPath = $tempDir . '/.htaccess';
	if (!file_exists($htaccessPath)) {
		$htaccessContent = <<<HTACCESS
	# Prevent direct access to files
	Order deny,allow
	Deny from all
	HTACCESS;
	
		file_put_contents($htaccessPath, $htaccessContent);
	}
	
function cleanupOldTempFiles($tempDir, $maxAgeMinutes = 30) {
	if (!is_dir($tempDir)) return;

	$now = time();
	$maxAge = $maxAgeMinutes * 60;

	foreach (glob($tempDir . '/*') as $item) {
		$name = basename($item);
		if ($name === '.htaccess') continue;

		if (is_file($item)) {
			if ($now - filemtime($item) > $maxAge) {
				@unlink($item);
			}
		} elseif (is_dir($item)) {
			$folderMtime = filemtime($item);
			if ($now - $folderMtime > $maxAge) {
				$files = glob($item . '/*');
				$allOld = true;
				foreach ($files as $f) {
					if (filemtime($f) > ($now - $maxAge)) {
						$allOld = false;
						break;
					}
				}
				if ($allOld) {
					foreach ($files as $f) {
						@unlink($f);
					}
					@rmdir($item);
				}
			}
		}
	}
}

cleanupOldTempFiles($tempDir);