<?php
// --- 1. GUARDIÁN Y SESIÓN ---
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] != 'admin') {
    die("Acceso denegado. No tienes permisos para esta acción.");
}

// --- 2. CONEXIÓN MANUAL A LA BD ---
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3306;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// --- 3. PROCESAR LA ELIMINACIÓN (¡CORREGIDO!) ---
if (isset($_POST['eliminar_comentario'])) {
    
    // El 'name' del formulario era 'id_comentario', así que lo recibimos así
    $id_comentario_a_borrar = intval($_POST['id_comentario']); 
    $id_proyecto = intval($_POST['id_proyecto']); // Para la redirección

    // ¡AQUÍ ESTÁ EL ARREGLO!
    // La consulta DELETE ahora usa la columna correcta: 'id_comentario_proyecto'
    $stmt_delete = $conn->prepare("DELETE FROM Comentarios_Proyecto WHERE id_comentario_proyecto = ?");
    $stmt_delete->bind_param("i", $id_comentario_a_borrar);
    
    if ($stmt_delete->execute()) {
        // ¡Éxito!
        header("Location: ../view_project.php?id=" . $id_proyecto . "&comment=deleted");
        exit();
    } else {
        die("Error al eliminar el comentario.");
    }
    
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>