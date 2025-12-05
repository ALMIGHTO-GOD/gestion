<?php
// --- CONEXIÓN A LA BASE DE DATOS ---
require_once '../config.php';
// Ahora $conn está disponible gracias a config.php

// --- OBTENER DATOS DEL FORMULARIO ---
$token = $_POST['token'];
$nueva_password = $_POST['password'];

// --- VALIDAR EL TOKEN ---
$sql = "SELECT id_usuario FROM Usuarios WHERE reset_token = ? AND reset_expira > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    // Token válido, actualizar la contraseña
    $usuario = $resultado->fetch_assoc();
    $id_usuario = $usuario['id_usuario'];
    
    $pass_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
    
    $sql_update = "UPDATE Usuarios SET contrasena = ?, reset_token = NULL, reset_expira = NULL WHERE id_usuario = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $pass_hash, $id_usuario);
    $stmt_update->execute();
    
    echo "<script>
        alert('Contraseña actualizada correctamente.');
        window.location.href='../login.html';
    </script>";
} else {
    echo "<script>
        alert('El enlace de recuperación es inválido o ha expirado.');
        window.location.href='../olvide.html';
    </script>";
}

$conn->close();
?>