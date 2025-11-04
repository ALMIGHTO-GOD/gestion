<?php
// --- 1. EL GUARDIÁN DE SEGURIDAD ---
session_start(); 
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html"); 
    exit();
}

// --- 2. OBTENER DATOS DEL USUARIO ---
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$rol_usuario = $_SESSION['rol_usuario'];

// --- 3. CONEXIÓN A LA BD ---
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3307;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) {
    $error_bd = "Error de conexión a la BD: " . $conn->connect_error;
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
          <li><a href="dashboard.php" class="active">Dashboard</a></li>
          <li><a href="submit_project.html">Submit Project</a></li> <?php if ($rol_usuario == 'admin'): ?>
            <li><a href="admin_panel.php">Admin</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="main-header__user-actions">
                <a href="submit_project.html" class="btn btn--primary" id="new-project-btn">+ New Project</a>
        <div class="user-greeting">
            ¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>!
            (<a href="logout.php">Salir</a>)
        </div>
      </div>
    </header>

    <main class="main-content">
      <section class="projects-section">
        <header class="projects-section__header">
          <h1>Your Projects</h1>
          <p>Manage and track your submissions.</p>
        </header>

        <div class="project-grid">

        <?php
        if (empty($error_bd)) {

            // --- 1. CAMBIO EN LA CONSULTA SQL (AHORA SÍ JALA LA FOTO) ---
            // Unimos 'Proyectos' (p) con 'Estados_Proyecto' (e) Y con 'Archivos_Proyecto' (a)
            // Usamos LEFT JOIN para que el proyecto se muestre aunque no tenga foto
            // y filtramos por a.tipo_archivo = 'foto'
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

                    // --- 2. CAMBIO EN LA TARJETA (MOSTRAMOS LA FOTO REAL) ---
                    // Verificamos si la foto existe. Si no, ponemos una de relleno.
                    $ruta_foto = "https://via.placeholder.com/400x250/cccccc/000000?text=Sin+Imagen"; // Relleno
                    if (!empty($proyecto['url_foto'])) {
                        $ruta_foto = htmlspecialchars($proyecto['url_foto']); 
                    }
        ?>

                    <article
                        class="project-card"
                        data-title="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>"
                        data-author="<?php echo htmlspecialchars($nombre_usuario); ?>"
                        data-status="<?php echo htmlspecialchars($proyecto['nombre_estado']); ?>"
                        data-image="<?php echo $ruta_foto; ?>" data-description="<?php echo htmlspecialchars($proyecto['descripcion_larga']); ?>"
                    >
                        <div class="project-card__image-container">
                            <img
                                src="<?php echo $ruta_foto; ?>" 
                                alt="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>"
                            />
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
        } else {
            echo "<p style='color: red;'>$error_bd</p>";
        }
        $conn->close();
        ?>
        
        </div>
      </section>
    </main>

        <div id="project-modal" class="modal-overlay">
      <div class="modal-content">
        <button id="modal-close-btn" class="modal-close">&times;</button>
        <img src="" alt="Imagen del proyecto" id="modal-image" class="modal-image" />
        <div class="modal-header">
          <h2 id="modal-title">Título del Proyecto</h2>
          <span id="modal-status" class="status-label"></span>
        </div>
        <p id="modal-author" class="modal-author"></p>
        <p id="modal-description" class="modal-description"></p>
      </div>
    </div>

    <script src="main.js" defer></script>
  </body>
</html>