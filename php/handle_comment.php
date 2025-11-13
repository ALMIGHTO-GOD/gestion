<?php
// --- 1. INICIAMOS SESIÓN Y VERIFICAMOS ---
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado. Debes iniciar sesión para comentar.");
}

// --- 2. CONEXIÓN MANUAL A LA BD ---
// (No usamos header.php porque esto no es una página, es un "motor")
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3307;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// --- 3. OBTENEMOS LOS DATOS ---
if (isset($_POST['publicar_comentario']) && !empty(trim($_POST['comentario']))) {
    
    $id_usuario = $_SESSION['id_usuario']; // El ID del que está comentando
    $id_proyecto = intval($_POST['id_proyecto']);
    $comentario = trim($_POST['comentario']);

    // --- 4. INSERTAMOS EL COMENTARIO ---
    $sql_insert = "INSERT INTO Comentarios_Proyecto (id_proyecto, id_usuario, comentario) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    // 'iis' = integer, integer, string
    $stmt_insert->bind_param("iis", $id_proyecto, $id_usuario, $comentario);
    
    if ($stmt_insert->execute()) {
        // ¡Éxito! Lo mandamos de regreso a la página del proyecto
        header("Location: view_project.php?id=" . $id_proyecto . "&comment=success");
        exit();
    } else {
        die("Error al guardar el comentario.");
    }
} else {
    // Si mandó el formulario vacío, solo lo regresamos
    $id_proyecto = intval($_POST['id_proyecto']);
    header("Location: view_project.php?id=" . $id_proyecto . "&comment=error");
    exit();
}
?>