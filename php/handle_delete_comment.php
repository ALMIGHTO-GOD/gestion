<?php
// --- 1. GUARDIÁN Y SESIÓN ---
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] != 'admin') {
    die("Acceso denegado. No tienes permisos para esta acción.");
}

// --- 2. CONEXIÓN MANUAL A LA BD ---
require_once '../config.php';`r`n// Ahora $conn está disponible gracias a config.php

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