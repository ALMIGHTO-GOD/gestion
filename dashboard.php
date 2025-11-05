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
<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MEDIA SPROUTS - Dashboard</title>
    <link rel="stylesheet" href="style.css" />     <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
    <style>
        .main-header__user-actions { display: flex; align-items: center; gap: 20px; }
        .user-greeting { color: #ffffff; font-weight: 500; }
        .user-greeting a { color: #f0a0a0; text-decoration: none; }
        .user-greeting a:hover { text-decoration: underline; }
        .status-label {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            background-color: #ddd;
            color: #555;
        }
        /* Estilo para que las imágenes de las tarjetas se vean bien */
        .project-card__image-container img {
            width: 100%;
            height: 250px; /* Dales una altura fija */
            object-fit: cover; /* Esto evita que la imagen se estire */
        }
    </style>
  </head>
  <body>
    <header class="main-header">
      <div class="main-header__logo">MEDIA SPROUTS</div>
      <nav class="main-header__nav">
        <ul>
          <li><a href="dashboard.php" class="active">Inicio</a></li>
          <li><a href="Proyecto_en_espera.html">Proyectos Subidos</a></li> <?php if ($rol_usuario == 'admin'): ?>
            <li><a href="admin_panel.php">Administracion</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="main-header__user-actions">
                <a href="submit_project.html" class="btn btn--primary" id="new-project-btn">+ Nuevo Proyecto</a>
        <div class="user-greeting">
            ¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>!
            (<a href="logout.php">Salir</a>)
        </div>
      </div>
    </header>

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