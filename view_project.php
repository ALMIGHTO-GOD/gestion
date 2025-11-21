<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'view_project'; 

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';
// (El header ya nos da $conn, $id_usuario, $rol_usuario)

// --- 3. OBTENER EL PROYECTO DE LA URL ---
if (!isset($_GET['id'])) {
    die("<main class='main-content'><p style='color: red;'>Error: No se especificó un proyecto.</p></main></body></html>");
}
$id_proyecto = intval($_GET['id']);

if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 4. CONSULTA SQL (PROYECTO + AUTOR) ---
// (Esta es la versión corregida y completa)
$sql_proyecto = "SELECT p.*, e.nombre_estado, 
                    u.id_usuario AS 'autor_id', 
                    u.nombre_completo AS 'autor_nombre', 
                    u.correo AS 'autor_correo', 
                    u.telefono AS 'autor_telefono'
                 FROM Proyectos AS p
                 JOIN Estados_Proyecto AS e ON p.id_estado_actual = e.id_estado
                 JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                 WHERE p.id_proyecto = ?";
$stmt_proyecto = $conn->prepare($sql_proyecto);
$stmt_proyecto->bind_param("i", $id_proyecto);
$stmt_proyecto->execute();
$resultado_proyecto = $stmt_proyecto->get_result();
if ($resultado_proyecto->num_rows == 0) {
     die("<main class='main-content'><p style='color: red;'>Error: El proyecto no existe.</p></main></body></html>");
}
$proyecto = $resultado_proyecto->fetch_assoc();


// --- 5. CONSULTA SQL (ARCHIVOS) ---
$sql_archivos = "SELECT tipo_archivo, url_archivo FROM Archivos_Proyecto WHERE id_proyecto = ?";
$stmt_archivos = $conn->prepare($sql_archivos);
$stmt_archivos->bind_param("i", $id_proyecto);
$stmt_archivos->execute();
$resultado_archivos = $stmt_archivos->get_result();


// --- 6. CONSULTA SQL (COMENTARIOS) ---
// (Esta es la versión corregida en una línea)
$sql_comentarios = "SELECT c.id_comentario_proyecto, c.comentario, c.fecha_publicacion, u.nombre_completo AS 'comentarista_nombre', u.rol AS 'comentarista_rol' FROM Comentarios_Proyecto AS c JOIN Usuarios AS u ON c.id_usuario = u.id_usuario WHERE c.id_proyecto = ? ORDER BY c.fecha_publicacion DESC";
$stmt_comentarios = $conn->prepare($sql_comentarios);
$stmt_comentarios->bind_param("i", $id_proyecto);
$stmt_comentarios->execute();
$resultado_comentarios = $stmt_comentarios->get_result();

// --- 7. CONSULTA SQL (TODOS LOS ESTADOS) ---
$todos_los_estados = $conn->query("SELECT id_estado, nombre_estado FROM Estados_Proyecto");
?>

<title><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?> - MEDIA SPROUTS</title>

<main class="project-view-container">
    
    <div class="project-view-header">
        <h1><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></h1>
        <div class="project-view-meta">
            Por: <strong><?php echo htmlspecialchars($proyecto['autor_nombre']); ?></strong>
            <span class="status-label"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
        </div>
    </div>

    <?php if ($rol_usuario == 'admin'): ?>
        <div class="admin-info-box">
            <h3>Información de Contacto del Creador (Solo visible para Admins)</h3>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($proyecto['autor_correo']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($proyecto['autor_telefono']); ?></p>
        </div>
        <div class="admin-action-panel">
            <h3>Panel de Administrador</h3>
            <form action="php/handle_status_change.php" method="POST">
                <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                <div class="form-group">
                    <label for="id_nuevo_estado">Cambiar estado del proyecto a:</label>
                    <select name="id_nuevo_estado" class="admin-select">
                        <?php
                        if ($todos_los_estados->num_rows > 0) {
                            mysqli_data_seek($todos_los_estados, 0);
                            while($estado = $todos_los_estados->fetch_assoc()) {
                                $selected = ($estado['id_estado'] == $proyecto['id_estado_actual']) ? 'selected' : '';
                                echo "<option value='" . $estado['id_estado'] . "' $selected>" . htmlspecialchars($estado['nombre_estado']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="cambiar_estado" class="btn-login">Guardar Estado</button>
            </form>
        </div>
    <?php endif; ?>
    <?php 
    // Mostramos este panel SOLO si eres el DUEÑO del proyecto
    if ($id_usuario == $proyecto['autor_id']): 
    ?>
        <div class="user-action-panel">
            <?php 
            // Si el estado es "Archivado por Usuario" (ID 7)...
            if ($proyecto['id_estado_actual'] == 7): 
            ?>
                <p>Este proyecto está archivado. ¿Quieres restaurarlo?</p>
                <form action="php/handle_archive.php" method="POST">
                    <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                    <input type="hidden" name="accion" value="restaurar">
                    <button type="submit" name="cambiar_estado_usuario" class="btn-login btn-success">Restaurar Proyecto</button>
                </form>
            <?php else: ?>
                <p>Si ya no quieres que este proyecto sea visible, puedes archivarlo.</p>
                <form action="php/handle_archive.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres ARCHIVAR este proyecto?');">
                    <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                    <input type="hidden" name="accion" value="archivar">
                    <button type="submit" name="cambiar_estado_usuario" class="btn-login btn-warn">Archivar Proyecto</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="project-view-content">
        <h3>Descripción Completa</h3>
        <p><?php echo nl2br(htmlspecialchars($proyecto['descripcion_larga'])); ?></p>
        
        <h3>Archivos del Proyecto</h3>
        <ul class="project-files-list">
            <?php
            if ($resultado_archivos->num_rows > 0) {
                mysqli_data_seek($resultado_archivos, 0);
                while($archivo = $resultado_archivos->fetch_assoc()):
                    $url = htmlspecialchars($archivo['url_archivo']);
                    
                    if ($archivo['tipo_archivo'] == 'foto') {
                        echo "<li><strong>Foto Principal:</strong><br><img src='$url' alt='Foto del Proyecto' class='project-view-image'></li>";
                    }
                    if ($archivo['tipo_archivo'] == 'documento') {
                        // Solo mostrar el botón si el usuario tiene permiso (dueño o admin)
                        if ($id_usuario == $proyecto['autor_id'] || $rol_usuario == 'admin') {
                            echo "<li><a href='php/download_document.php?id_proyecto=$id_proyecto' class='btn btn--primary' target='_blank'>Descargar Documento</a></li>";
                        } else {
                            echo "<li><span style='color: var(--text-muted);'>📄 Documento (Solo visible para el creador y administradores)</span></li>";
                        }
                    }
                    if ($archivo['tipo_archivo'] == 'enlace_externo') {
                        echo "<li><a href='$url' class='btn btn--primary' target='_blank'>Ver Enlace Externo (Video/Web)</a></li>";
                    }
                endwhile;
            } else {
                echo "<li>No se subieron archivos para este proyecto.</li>";
            }
            ?>
        </ul>
    </div>

    <div class="comment-section">
        <h3>Comentarios</h3>

        <form action="php/handle_comment.php" method="POST" class="comment-form">
            <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
            <div class="form-group">
                <label for="comentario">Deja un comentario</label>
                <textarea name="comentario" rows="3" required></textarea>
            </div>
            <button type="submit" name="publicar_comentario" class="btn-login">Publicar Comentario</button>
        </form>

        <div class="comment-list">
            <?php if ($resultado_comentarios->num_rows > 0): ?>
                <?php mysqli_data_seek($resultado_comentarios, 0); ?>
                <?php while($comentario = $resultado_comentarios->fetch_assoc()): ?>
                    <div class="comment-bubble">
                        <div class="comment-author">
                            <strong><?php echo htmlspecialchars($comentario['comentarista_nombre']); ?></strong>
                            
                            <?php if ($comentario['comentarista_rol'] == 'admin'): ?>
                                <span class="admin-tag">Admin</span>
                            <?php endif; ?>
                            
                            <span class="comment-date"><?php echo date('d/m/Y h:i a', strtotime($comentario['fecha_publicacion'])); ?></span>

                            <?php if ($rol_usuario == 'admin'): ?>
                                <form action="php/handle_delete_comment.php" method="POST" class="delete-comment-form" onsubmit="return confirm('¿Estás seguro de que quieres ELIMINAR este comentario?');">
                                    <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario_proyecto']; ?>">
                                    <input type="hidden" name="id_proyecto" value="<?php echo $id_proyecto; ?>">
                                    <button type="submit" name="eliminar_comentario" class="delete-comment-btn">&times; Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hay comentarios. ¡Sé el primero en comentar!</p>
            <?php endif; ?>
        </div>
    </div>

</main>

</body>
</html>
<?php
// Cerramos todas las conexiones
$stmt_proyecto->close();
$stmt_archivos->close();
$stmt_comentarios->close();
$todos_los_estados->close();
$conn->close();

ob_end_flush(); 
?>