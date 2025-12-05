<?php
// --- 1. GUARDIÁN DE SEGURIDAD E INICIO ---
session_start();
// Si no hay sesión iniciada, lo corremos
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.html");
    exit();
}
// Guardamos el ID del usuario que está subiendo el proyecto
$id_usuario_actual = $_SESSION['id_usuario'];

// --- 2. CONEXIÓN A LA BASE DE DATOS ---
require_once '../config.php';
// Ahora $conn está disponible gracias a config.php

// --- 3. CREAR LA CARPETA DE SUBIDAS (SI NO EXISTE) ---
// Los archivos se guardarán en "htdocs/media_sprouts/uploads/"
$carpeta_subidas = '../uploads/'; // Sube un nivel (de 'php' a raíz) y entra a 'uploads'
if (!is_dir($carpeta_subidas)) {
    mkdir($carpeta_subidas, 0755, true);
}

// --- 4. OBTENER DATOS DE TEXTO DEL FORMULARIO ---
$nombre_proyecto = $_POST['nombre_proyecto'];
$desc_breve = $_POST['descripcion_breve'];
$desc_larga = $_POST['descripcion_larga'];
$enlace_externo = $_POST['enlace_externo']; // Puede estar vacío

// El ID "1" corresponde a "Pendiente de Revisión" (según nuestro script de BD)
$id_estado_inicial = 1; 

$conn->begin_transaction(); // ¡Iniciamos transacción! Si algo falla, revertimos todo.

try {
    // --- 5. INSERTAR EL PROYECTO (SOLO TEXTO) ---
    // Guardamos el proyecto en la tabla 'Proyectos'
    $sql_proyecto = "INSERT INTO Proyectos (id_usuario, id_estado_actual, nombre_proyecto, descripcion_breve, descripcion_larga, visibilidad) 
                     VALUES (?, ?, ?, ?, ?, 'visible')";
    $stmt_proyecto = $conn->prepare($sql_proyecto);
    $stmt_proyecto->bind_param("iisss", $id_usuario_actual, $id_estado_inicial, $nombre_proyecto, $desc_breve, $desc_larga);
    $stmt_proyecto->execute();

    // ¡Obtenemos el ID del proyecto que ACABAMOS de crear!
    $id_proyecto_nuevo = $conn->insert_id;

    // --- 6. MANEJAR LOS ARCHIVOS ---

    // A. Manejar la FOTO (Obligatoria)
    if (isset($_FILES['foto_proyecto']) && $_FILES['foto_proyecto']['error'] == UPLOAD_ERR_OK) {
        $foto_info = $_FILES['foto_proyecto'];
        // Creamos un nombre único para evitar que se reemplacen archivos
        $foto_nombre = uniqid('foto_', true) . '.' . pathinfo($foto_info['name'], PATHINFO_EXTENSION);
        $foto_ruta_destino = $carpeta_subidas . $foto_nombre;
        
        if (move_uploaded_file($foto_info['tmp_name'], $foto_ruta_destino)) {
            // Guardamos la RUTA en la tabla 'Archivos_Proyecto'
            $ruta_guardar = 'uploads/' . $foto_nombre; // Ruta relativa para el HTML
            $sql_foto = "INSERT INTO Archivos_Proyecto (id_proyecto, tipo_archivo, url_archivo) VALUES (?, 'foto', ?)";
            $stmt_foto = $conn->prepare($sql_foto);
            $stmt_foto->bind_param("is", $id_proyecto_nuevo, $ruta_guardar);
            $stmt_foto->execute();
        } else {
            throw new Exception("Error al mover la foto.");
        }
    } else {
        throw new Exception("Error al subir la foto o no se proporcionó una.");
    }

    // B. Manejar el DOCUMENTO (Opcional)
    if (isset($_FILES['documento_proyecto']) && $_FILES['documento_proyecto']['error'] == UPLOAD_ERR_OK) {
        $doc_info = $_FILES['documento_proyecto'];
        $doc_nombre = uniqid('doc_', true) . '.' . pathinfo($doc_info['name'], PATHINFO_EXTENSION);
        $doc_ruta_destino = $carpeta_subidas . $doc_nombre;
        
        if (move_uploaded_file($doc_info['tmp_name'], $doc_ruta_destino)) {
            $ruta_guardar = 'uploads/' . $doc_nombre;
            $sql_doc = "INSERT INTO Archivos_Proyecto (id_proyecto, tipo_archivo, url_archivo) VALUES (?, 'documento', ?)";
            $stmt_doc = $conn->prepare($sql_doc);
            $stmt_doc->bind_param("is", $id_proyecto_nuevo, $ruta_guardar);
            $stmt_doc->execute();
        }
    }

    // C. Manejar el ENLACE (Opcional)
    if (!empty($enlace_externo)) {
        $sql_enlace = "INSERT INTO Archivos_Proyecto (id_proyecto, tipo_archivo, url_archivo) VALUES (?, 'enlace_externo', ?)";
        $stmt_enlace = $conn->prepare($sql_enlace);
        $stmt_enlace->bind_param("is", $id_proyecto_nuevo, $enlace_externo);
        $stmt_enlace->execute();
    }

    // --- 7. FINALIZAR ---
    $conn->commit(); // ¡Todo salió bien! Confirmamos los cambios.
    echo "¡Proyecto subido con éxito! Serás redirigido al dashboard.";
    // Redirigimos de vuelta al dashboard
   // Esta es la forma correcta (una ruta absoluta desde el localhost)
    header("Location: ../dashboard.php?upload=success");
    exit();

} catch (Exception $e) {
    // --- 8. MANEJO DE ERRORES ---
    $conn->rollback(); // ¡Algo falló! Revertimos todos los cambios en la BD.
    die("Error al subir el proyecto: " . $e->getMessage() . " <a href='../submit_project.html'>Inténtalo de nuevo</a>.");
}

$conn->close();
?>