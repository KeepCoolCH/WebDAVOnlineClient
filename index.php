<?php
/* WebDAV Online Client V.1.0
   Developed by Kevin Tobler
   www.kevintobler.ch
*/

session_start();
if (isset($_SESSION['user']) && isset($_SESSION['pass'])) {
    header("Location: webdav.php?action=list");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WebDAV Online Client</title>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="stylesheet" href="css/style.css">
	<script src="js/loadlist.js"></script>
</head>
<body>
	<?php if (!isset($_SESSION['user'])): ?>
    <form class="login-form" method="POST" action="webdav.php?action=list">
    <h2>WebDAV Login</h2><br>
    	<?php if (isset($_GET['error'])): ?>
	<div class="error-message">
    <?= htmlspecialchars($_GET['error']) ?>
	</div>
	<?php endif; ?>
    <div class="login"><input type="text" name="url" required placeholder="Server example.com:port (default port 80 or 443)"></div>
    <div class="login"><input type="text" name="user" required placeholder="Username"></div>
    <div class="login"><input type="password" name="pass" required placeholder="Password"></div><br>
		<button type="submit">🔐 Login</button>
    </form>
	<?php endif; ?>
<?php require_once 'inc/footer.php'; ?>
</body>
</html>
