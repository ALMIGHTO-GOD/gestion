<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'espera'; // Para que el header.php marque la pestaña activa

// --- 2. INCLUIR EL ENCABEZADO ---
// Esto jala el guardián de seguridad (¡debe estar logueado!) y la BD
require_once 'header.php';

// Si el header tuvo un error de BD, lo mostramos y morimos
if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 3. CONSULTA SQL PARA LA GALERÍA COMUNITARIA ---
// Jala TODOS los proyectos "Pendientes" (de TODOS los usuarios)
// y los junta con el nombre del autor y la foto
$sql_proyectos = "SELECT 
                    p.id_proyecto, p.nombre_proyecto, p.descripcion_breve,
                    e.nombre_estado, 
                    a.url_archivo AS 'url_foto',
                    u.nombre_completo AS 'autor'
                  FROM Proyectos AS p
                  JOIN Estados_Proyecto AS e ON p.id_estado_actual = e.id_estado
                  JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                  LEFT JOIN Archivos_Proyecto AS a ON p.id_proyecto = a.id_proyecto AND a.tipo_archivo = 'foto'
                  WHERE p.id_estado_actual = 1 OR p.id_estado_actual = 8"; // 1='Pendiente', 8='Pendiente(Actualizado)'
                        
$resultado_proyectos = $conn->query($sql_proyectos);
$conteo_proyectos = 0;
if ($resultado_proyectos) {
    $conteo_proyectos = $resultado_proyectos->num_rows;
}
?>

<title>Proyectos en Espera - Comunidad</title>

<main class="project-container">
  <h1>Proyectos en Espera</h1>
  <p class="subtitle">Lista de proyectos pendientes.</p>

    <div class="search-bar">
    <input type="text" id="search" placeholder="Buscar proyecto o equipo...">
    <div class="total">Total: <span id="count"><?php echo $conteo_proyectos; ?></span></div>
  </div>

    <div class="project-grid">
    <?php
    if ($conteo_proyectos > 0):
        mysqli_data_seek($resultado_proyectos, 0); 
        while($proyecto = $resultado_proyectos->fetch_assoc()):
            $ruta_foto = "https://via.placeholder.com/400x250/cccccc/000000?text=Sin+Imagen";
            if (!empty($proyecto['url_foto'])) {
                $ruta_foto = htmlspecialchars($proyecto['url_foto']); 
            }
    ?>
        <a href="view_project.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="project-card-link">
            <article class="project-card">
                <div class="project-card__image-container">
                    <img src="<?php echo $ruta_foto; ?>" alt="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>" />
                </div>
                <div class="project-card__content">
                    <h3><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></h3>
                    <p class="project-card__description">
                        <?php echo htmlspecialchars($proyecto['descripcion_breve']); ?>
                    </p>
                    <p class="project-card__author"><?php echo htmlspecialchars($proyecto['autor']); ?></p>
                    <span class="status-label"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
                </div>
            </article>
        </a>
    <?php
        endwhile; 
    else:
    ?>
        <p style="color: white;">¡No hay ningún proyecto en espera en la comunidad!</p>
    <?php
    endif; 
    $resultado_proyectos->close();
    ?>
  </div>
</main>

<script src="main.js" defer></script> 
</body>
</html>
<?php
// Cerramos la conexión a la BD que se abrió en el header
$conn->close();

ob_end_flush(); // <-- ¡ESTA ES LA LÍNEA NUEVA!
?>