<?php
session_start();

// --- 1. VERIFICAR QUE EL USUARIO ESTÉ LOGUEADO ---
if (!isset($_SESSION['id_usuario'])) {
    die("Error: Debes iniciar sesión para acceder a este documento.");
}

$id_usuario_actual = $_SESSION['id_usuario'];
$rol_usuario = $_SESSION['rol_usuario'];

// --- 2. OBTENER EL ID DEL PROYECTO ---
if (!isset($_GET['id_proyecto'])) {
    die("Error: No se especificó un proyecto.");
}
$id_proyecto = intval($_GET['id_proyecto']);

// --- 3. CONEXIÓN A LA BD ---
require_once '../config.php';`r`n// Ahora $conn está disponible gracias a config.php

// --- 4. OBTENER INFORMACIÓN DEL PROYECTO Y SU DUEÑO ---
$sql_proyecto = "SELECT id_usuario, nombre_proyecto FROM Proyectos WHERE id_proyecto = ?";
$stmt = $conn->prepare($sql_proyecto);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Error: El proyecto no existe.");
}

$proyecto = $resultado->fetch_assoc();
$id_dueno = $proyecto['id_usuario'];

// --- 5. VERIFICAR PERMISOS ---
// Solo el dueño del proyecto o un admin pueden descargar
if ($id_usuario_actual != $id_dueno && $rol_usuario != 'admin') {
    die("Error: No tienes permiso para acceder a este documento. Solo el creador del proyecto o un administrador pueden descargarlo.");
}

// --- 6. OBTENER EL DOCUMENTO ---
$sql_documento = "SELECT url_archivo FROM Archivos_Proyecto WHERE id_proyecto = ? AND tipo_archivo = 'documento' LIMIT 1";
$stmt_doc = $conn->prepare($sql_documento);
$stmt_doc->bind_param("i", $id_proyecto);
$stmt_doc->execute();
$resultado_doc = $stmt_doc->get_result();

if ($resultado_doc->num_rows == 0) {
    die("Error: No se encontró ningún documento para este proyecto.");
}

$documento = $resultado_doc->fetch_assoc();
$ruta_archivo = $documento['url_archivo'];

// --- 7. VERIFICAR QUE EL ARCHIVO EXISTA ---
$ruta_completa = "../" . $ruta_archivo; // Ajustar ruta relativa

if (!file_exists($ruta_completa)) {
    die("Error: El archivo no existe en el servidor.");
}

// --- 8. DESCARGAR EL ARCHIVO ---
$nombre_archivo = basename($ruta_archivo);
$tipo_mime = mime_content_type($ruta_completa);

header('Content-Description: File Transfer');
header('Content-Type: ' . $tipo_mime);
header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
header('Content-Length: ' . filesize($ruta_completa));
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($ruta_completa);

$conn->close();
exit();
?>
