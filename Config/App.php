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

define('STRIPE_PUBLIC_KEY', 'pk_test_51TJcCsJqZpMA15CnopcYjB1LTiEBFVTG9QxPFjDLxayUnoK5xBuQTaJss4TmD852aO3C3znUqFnOcbY8lEOuyewV00CfhKyUZK');
define('STRIPE_SECRET_KEY', 'sk_test_51TJcCsJqZpMA15CnxhYrWgSA2BFvN7auxH1AMsJiVDhwrVBaCBZcxYvZtfiwCmqQxkH346bOc9a36wTh7UxmtAxR00BcXTto3O');
?>
