<?php
session_start();

// --- 1. VERIFICAR SESIÓN Y ROL DE ADMIN ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol_usuario'] != 'admin') {
    header("Location: ../login.html");
    exit();
}

// --- 2. CONECTAR A LA BASE DE DATOS ---
$conn = new mysqli("localhost", "root", "", "media_sprouts", 3306);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// --- 3. VALIDAR DATOS DEL FORMULARIO ---
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: ../subir_promovido.php");
    exit();
}

$id_proyecto = isset($_POST['id_proyecto']) ? intval($_POST['id_proyecto']) : 0;
$url_multimedia = '';
$tipo_multimedia = '';

// Verificar si se subió un archivo o se proporcionó una URL
if (isset($_FILES['file_multimedia']) && $_FILES['file_multimedia']['error'] == UPLOAD_ERR_OK) {
    // CASO 1: Archivo subido
    $file = $_FILES['file_multimedia'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validar tamaño (50MB máximo)
    if ($file_size > 50 * 1024 * 1024) {
        die("Error: El archivo es demasiado grande. Máximo 50MB.");
    }
    
    // Validar extensión
    if (!in_array($file_ext, ['mp4', 'mp3'])) {
        die("Error: Solo se permiten archivos MP4 o MP3.");
    }
    
    // Crear directorio de uploads si no existe
    $upload_dir = '../uploads/multimedia/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generar nombre único para el archivo
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    // Mover archivo
    if (!move_uploaded_file($file_tmp, $upload_path)) {
        die("Error: No se pudo guardar el archivo.");
    }
    
    // Guardar ruta relativa
    $url_multimedia = 'uploads/multimedia/' . $new_filename;
    $tipo_multimedia = $file_ext; // 'mp4' o 'mp3'
    
} elseif (isset($_POST['url_multimedia']) && !empty(trim($_POST['url_multimedia']))) {
    // CASO 2: URL proporcionada
    $url_multimedia = trim($_POST['url_multimedia']);
    
    // Validar URL
    if (!filter_var($url_multimedia, FILTER_VALIDATE_URL)) {
        die("Error: URL de video inválida.");
    }
    
    // Detectar tipo de multimedia automáticamente
    $tipo_multimedia = 'enlace_youtube'; // valor por defecto
    
    if (stripos($url_multimedia, 'youtube.com') !== false || stripos($url_multimedia, 'youtu.be') !== false) {
        $tipo_multimedia = 'enlace_youtube';
    } elseif (stripos($url_multimedia, 'vimeo.com') !== false) {
        $tipo_multimedia = 'enlace_vimeo';
    } elseif (preg_match('/\.mp4$/i', $url_multimedia)) {
        $tipo_multimedia = 'mp4';
    } elseif (preg_match('/\.mp3$/i', $url_multimedia)) {
        $tipo_multimedia = 'mp3';
    }
} else {
    die("Error: Debes proporcionar una URL o subir un archivo.");
}

// Validar que el proyecto existe
if ($id_proyecto <= 0) {
    die("Error: ID de proyecto inválido.");
}

// (Validación redundante eliminada)

// Verificar que el proyecto existe
$sql_check = "SELECT id_proyecto FROM Proyectos WHERE id_proyecto = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id_proyecto);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    die("Error: El proyecto con ID $id_proyecto no existe.");
}
$stmt_check->close();

// --- 4. INSERTAR EN LA BASE DE DATOS ---
$sql_insert = "INSERT INTO historias_exito (id_proyecto, tipo_multimedia, url_multimedia) 
               VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("iss", $id_proyecto, $tipo_multimedia, $url_multimedia);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Redirigir a la galería con mensaje de éxito
    header("Location: ../proyectos_promovidos.php?success=1");
    exit();
} else {
    die("Error al guardar: " . $stmt->error);
}
?>
