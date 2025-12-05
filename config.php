<?php
/**
 * ============================================
 * CONFIGURACIÓN CENTRALIZADA DE BASE DE DATOS
 * ============================================
 * 
 * Este archivo detecta automáticamente si estás en:
 * - Ambiente LOCAL (XAMPP)
 * - Ambiente PRODUCCIÓN (Host)
 * 
 * Y carga las credenciales apropiadas.
 */

// Detectar ambiente: si el host es localhost o 127.0.0.1, es local
$is_local = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0
);

if ($is_local) {
    // ==========================================
    // CONFIGURACIÓN LOCAL (XAMPP)
    // ==========================================
    $servidor = "127.0.0.1";
    $usuario_db = "root";
    $pass_db = "";
    $db_nombre = "media_sprouts";
    $puerto = 3306;
    
} else {
    // ==========================================
    // CONFIGURACIÓN PRODUCCIÓN (Host)
    // ==========================================
    $servidor = "localhost";
    $usuario_db = "u168395992_ALMIGHTO";
    $pass_db = "ALmighto123";
    $db_nombre = "u168395992_media_sprout";
    $puerto = 3306;
}

// Crear la conexión a la base de datos
$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    // En producción, no mostrar detalles del error
    if ($is_local) {
        die("Error de conexión a la BD: " . $conn->connect_error);
    } else {
        die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
    }
}

// Configurar charset UTF-8 para evitar problemas con caracteres especiales
$conn->set_charset("utf8mb4");

// Variable para identificar el ambiente (útil para debugging)
$AMBIENTE = $is_local ? 'LOCAL' : 'PRODUCCION';
?>
