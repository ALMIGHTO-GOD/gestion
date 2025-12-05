<?php
// --- 1. GUARDIÁN Y SESIÓN ---
session_start();
// Si no hay sesión, lo corremos
if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado. Debes iniciar sesión.");
}
$id_usuario_actual = $_SESSION['id_usuario'];

// --- 2. CONEXIÓN MANUAL A LA BD ---
require_once '../config.php';`r`n// Ahora $conn está disponible gracias a config.php

// --- 3. PROCESAR EL CAMBIO DE ESTADO ---
if (isset($_POST['cambiar_estado_usuario'])) {
    
    $id_proyecto = intval($_POST['id_proyecto']);
    $accion = $_POST['accion']; // Será 'archivar' o 'restaurar'

    // IDs de estado de tu BD
    $ID_ESTADO_ARCHIVADO = 7;
    $ID_ESTADO_PENDIENTE = 1; // A dónde regresa un proyecto restaurado

    // --- 4. ¡VERIFICACIÓN DE SEGURIDAD! ---
    // Revisamos que el proyecto que intentas cambiar SÍ SEA TUYO.
    
    $stmt_check = $conn->prepare("SELECT id_usuario FROM Proyectos WHERE id_proyecto = ?");
    $stmt_check->bind_param("i", $id_proyecto);
    $stmt_check->execute();
    $resultado = $stmt_check->get_result();
    
    if ($resultado->num_rows == 0) {
        die("Error: El proyecto no existe.");
    }
    
    $id_dueno_proyecto = $resultado->fetch_assoc()['id_usuario'];
    $stmt_check->close();

    // Si el ID del dueño NO es el ID de la sesión...
    if ($id_dueno_proyecto != $id_usuario_actual) {
        die("Error: No tienes permiso para archivar este proyecto.");
    }

    // --- 5. SI ES EL DUEÑO, PROCEDEMOS ---
    $id_nuevo_estado = ($accion == 'archivar') ? $ID_ESTADO_ARCHIVADO : $ID_ESTADO_PENDIENTE;

    $stmt_update = $conn->prepare("UPDATE Proyectos SET id_estado_actual = ? WHERE id_proyecto = ?");
    $stmt_update->bind_param("ii", $id_nuevo_estado, $id_proyecto);
    
    if ($stmt_update->execute()) {
        // ¡Éxito! Lo mandamos de regreso a la página del proyecto
        header("Location: ../view_project.php?id=" . $id_proyecto . "&status=user_changed");
        exit();
    } else {
        die("Error al actualizar el estado.");
    }

} else {
    // Si llegó aquí por error, lo regresamos
    header("Location: ../dashboard.php");
    exit();
}
?>