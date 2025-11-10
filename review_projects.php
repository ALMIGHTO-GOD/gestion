<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'revisar';

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';

// --- 3. GUARDIÁN DE ADMIN (EXTRA) ---
// El header ya revisó si está logueado, ahora revisamos si es ADMIN
if ($rol_usuario != 'admin') {
    // Si no es admin, lo corremos al dashboard normal
    header("Location: dashboard.php");
    exit();
}

if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}
?>

<title>Revisar Proyectos - Admin</title>

    <main class="main-content">
      <section class="projects-section">
        <header class="projects-section__header">
          <h1>Proyectos Pendientes de Revisión</h1>
          <p>Aquí están todos los proyectos de usuarios que esperan aprobación.</p>
        </header>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nombre del Proyecto</th>
                    <th>Enviado por</th>
                    <th>Fecha de Envío</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- CONSULTA SQL PARA 'review_projects.php' ---
                // Jala TODOS los proyectos pendientes de CUALQUIER usuario
                $sql_pendientes = "SELECT 
                                    p.id_proyecto, p.nombre_proyecto, p.fecha_creacion,
                                    u.nombre_completo AS 'autor'
                                  FROM Proyectos AS p
                                  JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                                  WHERE p.id_estado_actual = 1 OR p.id_estado_actual = 8
                                  ORDER BY p.fecha_creacion ASC";
                                        
                $resultado_pendientes = $conn->query($sql_pendientes);
                
                if ($resultado_pendientes && $resultado_pendientes->num_rows > 0):
                    while($proyecto = $resultado_pendientes->fetch_assoc()):
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></td>
                            <td><?php echo htmlspecialchars($proyecto['autor']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($proyecto['fecha_creacion'])); ?></td>
                            <td><a href="view_project.php?id=<?php echo $proyecto['id_proyecto']; ?>">Revisar</a></td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">¡Felicidades! No hay proyectos pendientes.</td>
                    </tr>
                <?php
                endif; 
                ?>
            </tbody>
        </table>

      </section>
    </main>

  </body>
</html>
<?php
// Cerramos la conexión a la BD que se abrió en el header
$conn->close();
?>