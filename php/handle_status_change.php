<?php
// --- 1. GUARDIÁN Y SESIÓN ---
session_start();
// Si no hay sesión, o si el rol NO es 'admin', lo corremos.
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] != 'admin') {
    die("Acceso denegado. No tienes permisos para esta acción.");
}
$id_admin = $_SESSION['id_usuario'];

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

// --- 3. PROCESAR EL CAMBIO ---
if (isset($_POST['cambiar_estado'])) {
    
    $id_proyecto = intval($_POST['id_proyecto']);
    $id_nuevo_estado = intval($_POST['id_nuevo_estado']);

    // Para el historial, necesitamos saber cuál era el estado ANTERIOR
    $stmt_viejo = $conn->prepare("SELECT id_estado_actual FROM Proyectos WHERE id_proyecto = ?");
    $stmt_viejo->bind_param("i", $id_proyecto);
    $stmt_viejo->execute();
    $id_estado_anterior = $stmt_viejo->get_result()->fetch_assoc()['id_estado_actual'];
    $stmt_viejo->close();

    // Iniciamos una transacción. Si algo falla, revertimos todo.
    $conn->begin_transaction();

    try {
        // 1. Actualizamos el proyecto
        $stmt_update = $conn->prepare("UPDATE Proyectos SET id_estado_actual = ? WHERE id_proyecto = ?");
        $stmt_update->bind_param("ii", $id_nuevo_estado, $id_proyecto);
        $stmt_update->execute();
        $stmt_update->close();

        // 2. Guardamos un registro en el historial
        $stmt_historial = $conn->prepare("INSERT INTO Historial_Estados (id_proyecto, id_estado_anterior, id_estado_nuevo, id_admin_modifico) VALUES (?, ?, ?, ?)");
        $stmt_historial->bind_param("iiii", $id_proyecto, $id_estado_anterior, $id_nuevo_estado, $id_admin);
        $stmt_historial->execute();
        $stmt_historial->close();

        // 3. ¡Todo salió bien!
        $conn->commit();
        
        // ¡ÉXITO! Lo mandamos de regreso a la página de revisión.
        // Como el proyecto ya no estará "Pendiente", desaparecerá de la lista.
        header("Location: ../review_projects.php?status=changed");
        exit();

    } catch (Exception $e) {
        // ¡Algo falló! Revertimos los cambios
        $conn->rollback();
        die("Error al actualizar el estado: " . $e->getMessage());
    }

} else {
    // Si llegó aquí por error, lo regresamos
    header("Location: ../review_projects.php");
    exit();
}
?>
