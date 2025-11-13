<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'dashboard';

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';
// (El header ya nos da $conn, $id_usuario, $nombre_usuario, $rol_usuario)

// Si el header tuvo un error de BD, lo mostramos
if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 3. ¡LA NUEVA LÓGICA DE ADMIN! ---
// Preparamos la consulta SQL dependiendo del ROL
if ($rol_usuario == 'admin') {
    
    // --- CONSULTA DE ADMIN (¡MUESTRA TODO!) ---
    // Jala todos los proyectos y los junta con el nombre del autor
    $sql_proyectos = "SELECT 
                        p.*, 
                        e.nombre_estado, 
                        a.url_archivo AS 'url_foto',
                        u.nombre_completo AS 'autor_nombre'
                      FROM Proyectos AS p
                      JOIN Estados_Proyecto AS e ON p.id_estado_actual = e.id_estado
                      JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                      LEFT JOIN Archivos_Proyecto AS a ON p.id_proyecto = a.id_proyecto AND a.tipo_archivo = 'foto'
                      ORDER BY p.fecha_creacion DESC";
                        
    $stmt_proyectos = $conn->prepare($sql_proyectos);
    // No hay 'bind_param' porque no estamos filtrando

} else {
    
    // --- CONSULTA DE USUARIO (SOLO MUESTRA LO SUYO) ---
    // (Esta es la que ya tenías)
    $sql_proyectos = "SELECT 
                        p.*, 
                        e.nombre_estado, 
                        a.url_archivo AS 'url_foto'
                      FROM Proyectos AS p
                      JOIN Estados_Proyecto AS e ON p.id_estado_actual = e.id_estado
                      LEFT JOIN Archivos_Proyecto AS a ON p.id_proyecto = a.id_proyecto AND a.tipo_archivo = 'foto'
                      WHERE p.id_usuario = ?
                      ORDER BY p.fecha_creacion DESC";
                        
    $stmt_proyectos = $conn->prepare($sql_proyectos);
    $stmt_proyectos->bind_param("i", $id_usuario);
}

// Ejecutamos la consulta (la de admin o la de usuario)
$stmt_proyectos->execute();
$resultado_proyectos = $stmt_proyectos->get_result();
?>

<title>Dashboard - MEDIA SPROUTS</title>

<main class="main-content">
  <section class="projects-section">
    <header class="projects-section__header">
        
        <?php if ($rol_usuario == 'admin'): ?>
            <h1>Todos los Proyectos</h1>
            <p>Administra todos los proyectos de la plataforma.</p>
        <?php else: ?>
            <h1>Tus Proyectos</h1>
            <p>Revisa tus proyectos.</p>
        <?php endif; ?>

    </header>

    <div class="project-grid">

    <?php
    if ($resultado_proyectos->num_rows > 0):
        while($proyecto = $resultado_proyectos->fetch_assoc()):
            
            // Preparamos la foto
            $ruta_foto = "https://via.placeholder.com/400x250/cccccc/000000?text=Sin+Imagen";
            if (!empty($proyecto['url_foto'])) {
                $ruta_foto = htmlspecialchars($proyecto['url_foto']); 
            }

            // --- 5. AUTOR DINÁMICO ---
            // Si es admin, jala el nombre del autor de la BD
            // Si es usuario, solo usa el nombre de la sesión
            $autor = ($rol_usuario == 'admin') ? $proyecto['autor_nombre'] : $nombre_usuario;
    ?>
            <a href="view_project.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="project-card-link">
                <article class="project-card" 
                         data-title="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>"
                         data-author="<?php echo htmlspecialchars($autor); ?>"
                         data-status="<?php echo htmlspecialchars($proyecto['nombre_estado']); ?>"
                         data-image="<?php echo $ruta_foto; ?>"
                         data-description="<?php echo htmlspecialchars($proyecto['descripcion_larga']); ?>">
                    
                    <div class="project-card__image-container">
                        <img src="<?php echo $ruta_foto; ?>" alt="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>" />
                    </div>
                    <div class="project-card__content">
                        <h3><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></h3>
                        <p class="project-card__description">
                            <?php echo htmlspecialchars($proyecto['descripcion_breve']); ?>
                        </S>
                        <p class="project-card__author"><?php echo htmlspecialchars($autor); ?></p>
                        <span class="status-label"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
                    </div>
                </article>
            </a>
    <?php
        endwhile; 
    else:
    ?>
        <?php if ($rol_usuario == 'admin'): ?>
            <p style="color: white;">No hay ningún proyecto en la plataforma todavía.</p>
        <?php else: ?>
            <p style="color: white;">Aún no tienes proyectos. ¡Haz clic en "+ New Project" para subir el primero!</p>
        <?php endif; ?>
    <?php
    endif; 
    $stmt_proyectos->close();
    ?>
    </div>
  </section>
  </main>

    <div id="project-modal" class="modal-overlay">
       </div>

  <script src="main.js" defer></script>
  </body>
</html>
<?php
// Cerramos la conexión a la BD que se abrió en el header
$conn->close();
ob_end_flush();
?>