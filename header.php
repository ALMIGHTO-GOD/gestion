<?php ob_start();

// --- 1. EL GUARDIÁN Y LA SESIÓN ---
session_start(); 

// Si no existe la variable de sesión, lo corremos al login
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
$puerto = 3306;

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
    <link rel="stylesheet" href="css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <header class="main-header">
      <a class="main-header__logo" href="dashboard.php" style="color:white; text-decoration:none;">MEDIA SPROUTS</a>
      <nav class="main-header__nav">
        <ul>
          <li><a href="dashboard.php" <?php if (isset($pagina_actual) && $pagina_actual == 'dashboard') echo 'class="active"'; ?>>Inicio</a></li>
          <li><a href="proyectos_promovidos.php" <?php if (isset($pagina_actual) && $pagina_actual == 'promovidos') echo 'class="active"'; ?>>Proyectos Promovidos</a></li>
          <?php if ($rol_usuario == 'usuario'): ?>
            <li><a href="proyecto_en_espera.php" <?php if (isset($pagina_actual) && $pagina_actual == 'espera') echo 'class="active"'; ?>>Proyectos en espera</a></li>
          <?php endif; ?>

          <?php if ($rol_usuario == 'admin'): ?>
            <li><a href="review_projects.php" <?php if (isset($pagina_actual) && $pagina_actual == 'revisar') echo 'class="active"'; ?>>Revisar Proyectos</a></li>
            <li><a href="Admin_panel.php" <?php if (isset($pagina_actual) && $pagina_actual == 'admin') echo 'class="active"'; ?>>Panel de Administración</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <div class="main-header__user-actions">
        <?php if ($rol_usuario == 'usuario'): ?>
            <a href="submit_project.html" class="btn btn--primary" id="new-project-btn">+ Nuevo Proyecto</a>
        <?php endif; ?>
        <div class="user-greeting">
            ¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>!
            (<a href="php/logout.php">Salir</a>)
        </div>
      </div>
    </header>