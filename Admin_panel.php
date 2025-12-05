<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'admin'; 

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';
// (El header ya nos da el $id_usuario del admin logueado)

// --- 3. GUARDIÁN DE ADMIN ---
if ($rol_usuario != 'admin') {
    header("Location: dashboard.php");
    exit();
}

if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 4. LÓGICA DE ACCIONES DEL ADMIN ---

// --- ACCIÓN A: CAMBIAR ROL ---
if (isset($_POST['cambiar_rol'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $nuevo_rol = $_POST['nuevo_rol'] === 'admin' ? 'admin' : 'usuario';

    if ($usuario_id != $id_usuario) { // No puedes cambiar tu propio rol
        $stmt_rol = $conn->prepare("UPDATE Usuarios SET rol = ? WHERE id_usuario = ?");
        $stmt_rol->bind_param("si", $nuevo_rol, $usuario_id);
        $stmt_rol->execute();
        $mensaje = "Rol actualizado correctamente a '$nuevo_rol'.";
        $stmt_rol->close();
    } else {
        $mensaje = "Error: No puedes cambiar tu propio rol.";
    }
}

// --- ACCIÓN B: CAMBIAR ESTADO (TEMPORAL) ---
if (isset($_POST['cambiar_estado'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $nuevo_estado = $_POST['nuevo_estado'] === 'activo' ? 'activo' : 'suspendido';

    if ($usuario_id != $id_usuario) { // No puedes desactivarte a ti mismo
        $stmt_estado = $conn->prepare("UPDATE Usuarios SET estado_cuenta = ? WHERE id_usuario = ?");
        $stmt_estado->bind_param("si", $nuevo_estado, $usuario_id);
        $stmt_estado->execute();
        $mensaje = "Estado de la cuenta actualizado a '$nuevo_estado'.";
        $stmt_estado->close();
    } else {
        $mensaje = "Error: No puedes cambiar tu propio estado de cuenta.";
    }
}

// --- ACCIÓN C: ELIMINAR USUARIO (PERMANENTE) ---
if (isset($_POST['eliminar_usuario'])) {
    $usuario_id = intval($_POST['usuario_id']);
    if ($usuario_id != $id_usuario) { // No puedes eliminarte a ti mismo
        $stmt_del = $conn->prepare("DELETE FROM Usuarios WHERE id_usuario = ?");
        $stmt_del->bind_param("i", $usuario_id);
        $stmt_del->execute();
        $mensaje = "Usuario eliminado permanentemente.";
        $stmt_del->close();
    } else {
        $mensaje = "Error: No puedes eliminarte a ti mismo.";
    }
}


// --- 5. LÓGICA DE BÚSQUEDA (Modificada para jalar 'estado_cuenta') ---
$resultado = null;
$busqueda_activa = isset($_GET['q']) && !empty(trim($_GET['q']));

// Preparamos la consulta base (con la nueva columna)
$sql_base = "SELECT id_usuario, nombre_completo, correo, rol, estado_cuenta 
             FROM Usuarios";

if ($busqueda_activa) {
    // SI HAY BÚSQUEDA, FILTRAMOS (Y EXCLUIMOS AL ADMIN ACTUAL)
    $busqueda_query = "%" . $_GET['q'] . "%"; 
    $sql = $sql_base . " WHERE (nombre_completo LIKE ? OR correo LIKE ?) AND id_usuario != ?";
    $stmt_busqueda = $conn->prepare($sql);
    $stmt_busqueda->bind_param("ssi", $busqueda_query, $busqueda_query, $id_usuario);

} else {
    // SI NO HAY BÚSQUEDA, MOSTRAMOS TODOS (MENOS AL ADMIN ACTUAL)
    $sql = $sql_base . " WHERE id_usuario != ?";
    $stmt_busqueda = $conn->prepare($sql);
    $stmt_busqueda->bind_param("i", $id_usuario);
}

$stmt_busqueda->execute();
$resultado = $stmt_busqueda->get_result();

?>

<title>Admin Panel - MEDIA SPROUTS</title>

<main class="project-container">
  <h1>Gestión de Usuarios</h1>
  <p class="subtitle">Panel para buscar usuarios y asignar roles.</p>

    <?php if (isset($mensaje)): ?>
    <p style="color: #38bdf8; font-weight: bold;"><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>

    <form method="get" class="search-bar" action="Admin_panel.php">
    <input type="text" name="q" id="search" placeholder="Buscar usuario por nombre o correo..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
    <button type="submit" class="btn btn--primary">Buscar</button>
  </form>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nombre completo</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Estado</th>           <th style="width: 35%;">Acciones</th>         </tr>
      </thead>
      <tbody>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
                      <tr <?php if ($fila['estado_cuenta'] == 'suspendido') echo 'style="background-color: #444;"'; ?>>
            <td><?php echo htmlspecialchars($fila['nombre_completo']); ?></td>
            <td><?php echo htmlspecialchars($fila['correo']); ?></td>
            <td><strong><?php echo htmlspecialchars($fila['rol']); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($fila['estado_cuenta']); ?></strong></td>
            <td class="cell-actions">
                <?php $queryString = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>
              <div class="actions-grid-container"> 
                <form method="post" class="admin-action-form" action="Admin_panel.php?q=<?php echo $queryString; ?>">
                <input type="hidden" name="usuario_id" value="<?php echo $fila['id_usuario']; ?>">
                <?php if ($fila['rol'] === 'admin'): ?>
                  <input type="hidden" name="nuevo_rol" value="usuario">
                  <button type="submit" name="cambiar_rol" class="btn-login btn-admin-action btn-warn">Quitar Credenciales</button>
                <?php else: ?>
                  <input type="hidden" name="nuevo_rol" value="admin">
                  <button type="submit" name="cambiar_rol" class="btn-login btn-admin-action btn-success">Dar Credenciales</button>
                <?php endif; ?>
              </form>
                
                <form method="post" class="admin-action-form" action="Admin_panel.php?q=<?php echo $queryString; ?>">
                <input type="hidden" name="usuario_id" value="<?php echo $fila['id_usuario']; ?>">
                <?php if ($fila['estado_cuenta'] === 'activo'): ?>
                  <input type="hidden" name="nuevo_estado" value="suspendido">
                  <button type="submit" name="cambiar_estado" class="btn-login btn-admin-action btn-warn">Desactivar</button>
                <?php else: ?>
                  <input type="hidden" name="nuevo_estado" value="activo">
                  <button type="submit" name="cambiar_estado" class="btn-login btn-admin-action btn-success">Activar</button>
                <?php endif; ?>
              </form>
                
                <form method="post" class="admin-action-form" 
                      action="Admin_panel.php?q=<?php echo $queryString; ?>"
                      onsubmit="return confirm('¿Estás seguro de que quieres ELIMINAR a este usuario? Esta acción es PERMANENTE y no se puede deshacer.');">
                <input type="hidden" name="usuario_id" value="<?php echo $fila['id_usuario']; ?>">
                <button type="submit" name="eliminar_usuario" class="btn-login btn-admin-action btn-danger">Eliminar</button>
              </form>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php elseif ($busqueda_activa): ?>
    <p style="color: white; font-size: 1.2rem; text-align: center;">No se encontraron usuarios.</p>
  <?php else: ?>
    <p style="color: white; font-size: 1.2rem; text-align: center;">No hay otros usuarios registrados en el sistema.</p>
  <?php endif; ?>
</main>

</body>
</html>
<?php
if (isset($stmt_busqueda)) $stmt_busqueda->close();
$conn->close();

ob_end_flush(); 
?>