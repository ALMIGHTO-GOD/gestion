<?php
// --- Conexión a base de datos ---
$conexion = new mysqli("localhost", "root", "", "media_sprouts");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

// --- Si se envía una acción de cambio de rol ---
if (isset($_POST['cambiar_rol'])) {
  $usuario_id = intval($_POST['usuario_id']);
  $nuevo_rol = $_POST['nuevo_rol'] === 'admin' ? 'admin' : 'usuario';

  $conexion->query("UPDATE Usuarios SET rol = '$nuevo_rol' WHERE id_usuario = $usuario_id");
  $mensaje = "Rol actualizado correctamente a '$nuevo_rol'.";
}

// --- Buscar usuario ---
$resultado = null;
if (isset($_GET['q'])) {
  $busqueda = $conexion->real_escape_string($_GET['q']);
  $resultado = $conexion->query("
      SELECT id_usuario, nombre_completo, correo, rol 
      FROM Usuarios 
      WHERE nombre_completo LIKE '%$busqueda%' 
      OR correo LIKE '%$busqueda%'
  ");
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MEDIA SPROUTS - Panel de Administración</title>

  <!-- 🔗 Tu archivo CSS externo -->
  <link rel="stylesheet" href="css/Admin_panel.css" />
    <style>
        .main-header__user-actions { display: flex; align-items: center; gap: 20px; }
        .user-greeting { color: #ffffff; font-weight: 500; }
        .user-greeting a { color: #f0a0a0; text-decoration: none; }
        .user-greeting a:hover { text-decoration: underline; }
    </style>  
</head>

<body>
  <header class="main-header">
    <div class="main-header__logo">MEDIA SPROUTS</div>
    <div>Panel de administración</div>
  </header>

  <section class="user-management">
    <h2>Gestión de usuarios</h2>

    <!-- Mostrar mensaje de acción -->
    <?php if (isset($mensaje)): ?>
      <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <!-- 🔍 Buscador -->
    <form method="get" class="user-search-form">
      <input type="text" name="q" placeholder="Buscar usuario por nombre o correo..." class="user-search-input" required>
      <button type="submit" class="btn btn--search">Buscar</button>
    </form>

    <!-- 🔽 Resultados -->
    <?php if ($resultado && $resultado->num_rows > 0): ?>
      <table class="tabla-usuarios">
        <thead>
          <tr>
            <th>Nombre completo</th>
            <th>Correo</th>
            <th>Rol actual</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
              <td><?= htmlspecialchars($fila['correo']) ?></td>
              <td><?= htmlspecialchars($fila['rol']) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="usuario_id" value="<?= $fila['id_usuario'] ?>">
                  <?php if ($fila['rol'] === 'admin'): ?>
                    <input type="hidden" name="nuevo_rol" value="usuario">
                    <button type="submit" name="cambiar_rol" class="btn btn--user">Quitar credenciales</button>
                  <?php else: ?>
                    <input type="hidden" name="nuevo_rol" value="admin">
                    <button type="submit" name="cambiar_rol" class="btn btn--admin">Dar credenciales</button>
                  <?php endif; ?>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php elseif (isset($_GET['q'])): ?>
      <p class="sin-resultados">No se encontraron usuarios.</p>
    <?php endif; ?>
  </section>
</body>
</html>
