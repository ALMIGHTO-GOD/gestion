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

<style>
    /* Estilos específicos para esta página */
    .review-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .review-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .review-header h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
        background: linear-gradient(to right, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .review-header p {
        color: var(--text-muted);
        font-size: 1.1rem;
    }

    .review-card {
        background-color: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        background-color: rgba(255, 255, 255, 0.03);
        text-align: left;
        padding: 16px 24px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--border);
    }

    .modern-table td {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-main);
        vertical-align: middle;
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .modern-table tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .project-name {
        font-weight: 600;
        font-size: 1.05rem;
        display: block;
        margin-bottom: 4px;
    }

    .project-author {
        font-size: 0.9rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .project-date {
        font-family: monospace;
        color: var(--text-muted);
        background: rgba(255, 255, 255, 0.05);
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .btn-review {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: rgba(56, 189, 248, 0.1);
        color: var(--primary);
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        border: 1px solid rgba(56, 189, 248, 0.2);
    }

    .btn-review:hover {
        background-color: var(--primary);
        color: var(--bg-body);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.25);
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-icon {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
        display: block;
    }
</style>

<div class="review-container">
    <header class="review-header">
        <h1>Revisión de Proyectos</h1>
        <p>Gestiona y aprueba las nuevas propuestas de la comunidad.</p>
    </header>

    <div class="review-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th width="45%">Proyecto</th>
                    <th width="25%">Fecha de Envío</th>
                    <th width="15%" style="text-align: right;">Acción</th>
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
                            <td>
                                <span class="project-name"><?php echo htmlspecialchars($proyecto['nombre_proyecto']); ?></span>
                                <span class="project-author">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <?php echo htmlspecialchars($proyecto['autor']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="project-date"><?php echo date('d M, Y', strtotime($proyecto['fecha_creacion'])); ?></span>
                            </td>
                            <td style="text-align: right;">
                                <a href="view_project.php?id=<?php echo $proyecto['id_proyecto']; ?>" class="btn-review">
                                    Revisar
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                            </td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="3">
                            <div class="empty-state">
                                <span class="empty-icon">🎉</span>
                                <h3>¡Todo al día!</h3>
                                <p>No hay proyectos pendientes de revisión en este momento.</p>
                            </div>
                        </td>
                    </tr>
                <?php
                endif; 
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php
// Cerramos la conexión a la BD que se abrió en el header
$conn->close();
?>