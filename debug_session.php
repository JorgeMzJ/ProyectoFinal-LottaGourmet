<?php
session_start();
echo "<h2>Debug de Sesión</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "Contenido de \$_SESSION:\n";
print_r($_SESSION);
echo "\n\nVerificaciones:\n";
echo "isset(\$_SESSION['usuario_admin']): " . (isset($_SESSION['usuario_admin']) ? 'true' : 'false') . "\n";
echo "Valor de \$_SESSION['usuario_admin']: " . ($_SESSION['usuario_admin'] ?? 'NO DEFINIDO') . "\n";
echo "Tipo: " . gettype($_SESSION['usuario_admin'] ?? null) . "\n";
echo "\nCondición (!isset(\$_SESSION['usuario_admin']) || \$_SESSION['usuario_admin'] != 1): ";
echo (!isset($_SESSION['usuario_admin']) || $_SESSION['usuario_admin'] != 1) ? 'TRUE (NO ES ADMIN)' : 'FALSE (ES ADMIN)';
echo "\n</pre>";
?>
