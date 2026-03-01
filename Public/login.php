<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');

$host = "localhost"; $user = "root"; $pass = ""; $db_name = "pastelesupbc";
$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) die(json_encode(["error" => "Fallo DB"]));

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

$email = $datos['email'] ?? '';
$password = $datos['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
    // Verifica el hash de la contraseña
    if (password_verify($password, $row['password_hash'])) {
        echo json_encode([
            "success" => true,
            "usuario" => [ "id" => $row['id'], "nombre" => $row['nombre'] ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
}
$stmt->close();
$conn->close();
?>