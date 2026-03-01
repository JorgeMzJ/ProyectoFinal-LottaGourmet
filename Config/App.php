<?php
// Dynamically determine BASE_URL so the app works when deployed
// at the webserver root or in a subfolder. Result always ends with '/'.
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$base = rtrim($scriptName, '/\\');
if ($base === '') {
	$base = '/';
} else {
	$base .= '/';
}
define('BASE_URL', $base);
?>
