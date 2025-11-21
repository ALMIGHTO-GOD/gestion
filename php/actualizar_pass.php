<?php
// --- 1. CONEXIÓN A LA BASE DE DATOS (otra vez) ---
$servidor = "127.0.0.1";
$usuario_db = "root";
$pass_db = "";
$db_nombre = "media_sprouts";
$puerto = 3306;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- 2. OBTENER DATOS DEL FORMULARIO ---
if (isset($_POST['token']) && isset($_POST['nueva_pass']) && isset($_POST['confirmar_pass'])) {
    
    $token = $_POST['token'];
    $nueva_pass = $_POST['nueva_pass'];
    $confirmar_pass = $_POST['confirmar_pass'];

    // --- 3. VALIDAR ---
    if ($nueva_pass !== $confirmar_pass) {
        die("Las contraseñas no coinciden. Regresa a <a href='olvide.html'>recuperar contraseña</a>");
    }

    // Buscamos el token en la BD y checamos que no haya expirado
    $sql = "SELECT id_usuario FROM Usuarios WHERE reset_token = ? AND reset_expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // ¡Token válido!
        $fila = $resultado->fetch_assoc();
        $id_usuario = $fila['id_usuario'];

        // --- 4. ACTUALIZAR CONTRASEÑA ---
        // ¡IMPORTANTE! Hashear la contraseña antes de guardarla
        $pass_hash = password_hash($nueva_pass, PASSWORD_DEFAULT);

        // Actualizamos la contraseña y borramos el token para que no se re-use
        $sql_update = "UPDATE Usuarios SET contrasena = ?, reset_token = NULL, reset_expira = NULL WHERE id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $pass_hash, $id_usuario);
        
        if ($stmt_update->execute()) {
            echo "¡Contraseña actualizada con éxito! Ya puedes <a href='login.html'>iniciar sesión</a>.";
        } else {
            echo "Error al actualizar la contraseña.";
        }
    } else {
        echo "Token inválido o expirado. Por favor, solicita un nuevo enlace.";
    }
}
$conn->close();
?>