<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
// Esta variable la usará el header.php para poner la clase "active"
$pagina_actual = 'dashboard';

// --- 2. INCLUIR EL ENCABEZADO ---
// Esto incluye el guardián de seguridad, la conexión a BD y todo el <header>
require_once 'header.php';

// Si el header tuvo un error de BD, lo mostramos y morimos
if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}
?>

    <main class="main-content">
      <section class="projects-section">
        <header class="projects-section__header">
          <h1>Tus Proyectos</h1>
          <p>Sigue y revisa tus proyectos.</p>
        </header>

        <div class="project-grid">

        <?php
        // --- CONSULTA SQL PARA 'dashboard.php' ---
        // Jala solo los proyectos del usuario que inició sesión
        $sql_proyectos = "SELECT 
                            p.*, 
                            e.nombre_estado, 
                            a.url_archivo AS 'url_foto'
                          FROM Proyectos AS p
                          JOIN Estados_Proyecto AS e ON p.id_estado_actual = e.id_estado
                          LEFT JOIN Archivos_Proyecto AS a ON p.id_proyecto = a.id_proyecto AND a.tipo_archivo = 'foto'
                          WHERE p.id_usuario = ?";
                                
        $stmt_proyectos = $conn->prepare($sql_proyectos);
        $stmt_proyectos->bind_param("i", $id_usuario);
        $stmt_proyectos->execute();
        $resultado_proyectos = $stmt_proyectos->get_result();
        
        if ($resultado_proyectos->num_rows > 0):
            while($proyecto = $resultado_proyectos->fetch_assoc()):
                $ruta_foto = "https://via.placeholder.com/400x250/cccccc/000000?text=Sin+Imagen";
                if (!empty($proyecto['url_foto'])) {
                    $ruta_foto = htmlspecialchars($proyecto['url_foto']); 
                }
        ?>
                <article class="project-card" ... (etc.) ... >
                    <div class="project-card__image-container">
                        <img src="<?php echo $ruta_foto; ?>" alt="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>" />
                    </div>
                    <div class="project-card__content">
                        <h3><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></h3>
                        <p class="project-card__description">
                            <?php echo htmlspecialchars($proyecto['descripcion_breve']); ?>
                        </p>
                        <p class="project-card__author"><?php echo htmlspecialchars($nombre_usuario); ?></p>
                        <span class="status-label"><?php echo htmlspecialchars($proyecto['nombre_estado']); ?></span>
                    </div>
                </article>
        <?php
            endwhile; 
        else:
        ?>
            <p style="color: white;">Aún no tienes proyectos. ¡Haz clic en "+ New Project" para subir el primero!</p>
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

ob_end_flush(); // <-- ¡ESTA ES LA LÍNEA NUEVA!
?>