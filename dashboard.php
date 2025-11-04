<?php
// --- 1. EL GUARDIÁN DE SEGURIDAD ---
// Iniciamos la sesión para revisar la "mochila"
session_start(); 

// Si no existe la variable de sesión, significa que NO ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Lo corremos de volada al login
    header("Location: login.html"); // (O 'iniciar_sesion.html' si no lo renombraste)
    exit(); // Detenemos el script
}

// --- 2. OBTENER DATOS DEL USUARIO ---
// Si llegó hasta aquí, SÍ inició sesión. Recuperamos sus datos.
$id_usuario = $_SESSION['id_usuario'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$rol_usuario = $_SESSION['rol_usuario'];


// --- 3. CONEXIÓN A LA BD PARA JALAR PROYECTOS ---
// (Esto es nuevo: vamos a jalar los proyectos DE VERDAD)
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3307;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) {
    // Si falla la BD, al menos muestra la página, pero con un error
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
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            background-color: #ddd;
            color: #555;
        }
    </style>
  </head>
  <body>
    <header class="main-header">
            <div class="main-header__logo">MEDIA SPROUTS</div>
      <nav class="main-header__nav">
        <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
          <li><a href="submit_project.php">Submit Project</a></li>
          
          <?php if ($rol_usuario == 'admin'): ?>
            <li><a href="admin_panel.php">Admin</a></li>
          <?php endif; ?>

        </ul>
      </nav>

      <div class="main-header__user-actions">
                <button class="btn btn--primary" id="new-project-btn">+ New Project</button>
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
        // Si no hay error en la BD, buscamos los proyectos
        if (empty($error_bd)) {

            // Preparamos la consulta para jalar los proyectos de ESTE usuario
            // y unimos con la tabla de Estados para saber su nombre
            $sql_proyectos = "SELECT 
                                Proyectos.*, 
                                Estados_Proyecto.nombre_estado 
                              FROM Proyectos 
                              JOIN Estados_Proyecto ON Proyectos.id_estado_actual = Estados_Proyecto.id_estado
                              WHERE Proyectos.id_usuario = ?";
                                    
            $stmt_proyectos = $conn->prepare($sql_proyectos);
            $stmt_proyectos->bind_param("i", $id_usuario);
            $stmt_proyectos->execute();
            $resultado_proyectos = $stmt_proyectos->get_result();
            
            // Si encontramos proyectos, los mostramos uno por uno
            if ($resultado_proyectos->num_rows > 0):
                while($proyecto = $resultado_proyectos->fetch_assoc()):
        ?>

                    <article
                        class="project-card"
                        data-title="<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>"
                        data-author="<?php echo htmlspecialchars($nombre_usuario); ?>"
                        data-status="<?php echo htmlspecialchars($proyecto['nombre_estado']); ?>"
                        data-image="https://via.placeholder.com/600x300/3c4b3f/ffffff?text=Project"
                        data-description="<?php echo htmlspecialchars($proyecto['descripcion_larga']); ?>"
                    >
                        <div class="project-card__image-container">
                            <img
                                src="https://via.placeholder.com/400x250/3c4b3f/ffffff?text=<?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?>"
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
                endwhile; // Fin del loop 'while'
            else:
                // Si el usuario no tiene proyectos
        ?>
            <p style="color: white;">Aún no tienes proyectos. ¡Haz clic en "+ New Project" para subir el primero!</p>
        <?php
            endif; // Fin del 'if num_rows'
            
            // Cerramos las conexiones
            $stmt_proyectos->close();

        } else {
            // Si hubo un error de BD, lo mostramos
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