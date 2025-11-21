<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'promovidos';

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';

// Si el header tuvo un error de BD, lo mostramos
if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 3. VERIFICAR QUE SEA ADMIN ---
if ($rol_usuario != 'admin') {
    die("<main class='main-content'><p style='color: red;'>Acceso denegado. Solo administradores pueden ver esta página.</p></main></body></html>");
}

// --- 4. OBTENER PROYECTOS PUBLICADOS (id_estado_actual = 6) ---
$sql_proyectos = "SELECT p.id_proyecto, p.nombre_proyecto, u.nombre_completo AS autor
                  FROM Proyectos AS p
                  JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                  WHERE p.id_estado_actual = 6
                  ORDER BY p.nombre_proyecto ASC";

$resultado_proyectos = $conn->query($sql_proyectos);
$proyectos_disponibles = [];
if ($resultado_proyectos && $resultado_proyectos->num_rows > 0) {
    while ($row = $resultado_proyectos->fetch_assoc()) {
        $proyectos_disponibles[] = $row;
    }
}
?>

<title>Subir Proyecto Promovido - MEDIA SPROUTS</title>

<main class="main-content">
  <div class="submit-project-container">
    <h2>Nueva Publicación Promovida</h2>
    <p>Agrega un video promocional de un proyecto publicado.</p>

    <form action="php/handle_promovido.php" method="POST" enctype="multipart/form-data">
      
      <div class="form-group">
        <label for="proyecto_search">Buscar Proyecto *</label>
        <input 
          type="text" 
          id="proyecto_search" 
          placeholder="Escribe el nombre del proyecto..."
          autocomplete="off"
          onkeyup="filterProjects()"
        >
        <div id="project_dropdown" class="project-dropdown" style="display: none;">
          <!-- Los resultados se mostrarán aquí -->
        </div>
        <small style="color: var(--text-muted); display: block; margin-top: 5px;">
          Solo se muestran proyectos publicados (estado 6).
        </small>
      </div>

      <input type="hidden" id="id_proyecto" name="id_proyecto" required>

      <div class="form-group" id="selected_project_info" style="display: none;">
        <label>Proyecto Seleccionado</label>
        <div style="background-color: var(--bg-card); padding: 15px; border-radius: 8px; border: 1px solid var(--border);">
          <p style="margin: 0; color: var(--text-main); font-weight: bold;" id="selected_name"></p>
          <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
            Autor: <span id="selected_author"></span>
          </p>
        </div>
      </div>

      <div class="form-group">
        <label>Contenido Multimedia *</label>
        <div style="margin-bottom: 15px;">
          <label style="display: inline-flex; align-items: center; margin-right: 20px; cursor: pointer;">
            <input type="radio" name="media_type" value="url" checked onchange="toggleMediaInput()">
            <span style="margin-left: 8px;">Enlace (URL)</span>
          </label>
          <label style="display: inline-flex; align-items: center; cursor: pointer;">
            <input type="radio" name="media_type" value="file" onchange="toggleMediaInput()">
            <span style="margin-left: 8px;">Subir Archivo</span>
          </label>
        </div>

        <!-- Opción URL -->
        <div id="url_input_container">
          <input 
            type="url" 
            id="url_multimedia" 
            name="url_multimedia" 
            placeholder="https://youtube.com/watch?v=... o https://vimeo.com/..." 
          >
          <small style="color: var(--text-muted); display: block; margin-top: 5px;">
            Acepta: YouTube, Vimeo, o enlaces directos a archivos MP4/MP3.
          </small>
        </div>

        <!-- Opción Archivo -->
        <div id="file_input_container" style="display: none;">
          <input 
            type="file" 
            id="file_multimedia" 
            name="file_multimedia" 
            accept="video/mp4,audio/mp3"
            style="padding: 10px; background-color: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; color: var(--text-main); width: 100%;"
          >
          <small style="color: var(--text-muted); display: block; margin-top: 5px;">
            Formatos permitidos: MP4 (video) o MP3 (audio). Máximo 50MB.
          </small>
        </div>
      </div>

      <button type="submit" class="btn-login">Publicar Proyecto Promovido</button>
      <a href="proyectos_promovidos.php" class="btn-login" style="background-color: var(--text-muted); display: inline-block; text-align: center; margin-left: 10px;">Cancelar</a>
    </form>

  </div>
</main>

<script>
// Array de proyectos disponibles desde PHP
const proyectos = <?php echo json_encode($proyectos_disponibles); ?>;

function filterProjects() {
  const searchInput = document.getElementById('proyecto_search');
  const searchTerm = searchInput.value.toLowerCase();
  const dropdown = document.getElementById('project_dropdown');
  
  if (searchTerm.length === 0) {
    dropdown.style.display = 'none';
    return;
  }
  
  // Filtrar proyectos que coincidan con el término de búsqueda
  const filtered = proyectos.filter(p => 
    p.nombre_proyecto.toLowerCase().includes(searchTerm) ||
    p.autor.toLowerCase().includes(searchTerm)
  );
  
  if (filtered.length === 0) {
    dropdown.innerHTML = '<div class="dropdown-item" style="color: var(--text-muted);">No se encontraron proyectos</div>';
    dropdown.style.display = 'block';
    return;
  }
  
  // Mostrar resultados
  dropdown.innerHTML = filtered.map(p => `
    <div class="dropdown-item" onclick="selectProject(${p.id_proyecto}, '${escapeHtml(p.nombre_proyecto)}', '${escapeHtml(p.autor)}')">
      <strong>${escapeHtml(p.nombre_proyecto)}</strong><br>
      <small style="color: var(--text-muted);">Por: ${escapeHtml(p.autor)}</small>
    </div>
  `).join('');
  
  dropdown.style.display = 'block';
}

function selectProject(id, nombre, autor) {
  // Guardar el ID en el campo oculto
  document.getElementById('id_proyecto').value = id;
  
  // Mostrar información del proyecto seleccionado
  document.getElementById('selected_name').textContent = nombre;
  document.getElementById('selected_author').textContent = autor;
  document.getElementById('selected_project_info').style.display = 'block';
  
  // Limpiar búsqueda y ocultar dropdown
  document.getElementById('proyecto_search').value = '';
  document.getElementById('project_dropdown').style.display = 'none';
}

function toggleMediaInput() {
  const mediaType = document.querySelector('input[name="media_type"]:checked').value;
  const urlContainer = document.getElementById('url_input_container');
  const fileContainer = document.getElementById('file_input_container');
  const urlInput = document.getElementById('url_multimedia');
  const fileInput = document.getElementById('file_multimedia');
  
  if (mediaType === 'url') {
    urlContainer.style.display = 'block';
    fileContainer.style.display = 'none';
    urlInput.required = true;
    fileInput.required = false;
  } else {
    urlContainer.style.display = 'none';
    fileContainer.style.display = 'block';
    urlInput.required = false;
    fileInput.required = true;
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(e) {
  const dropdown = document.getElementById('project_dropdown');
  const searchInput = document.getElementById('proyecto_search');
  
  if (e.target !== searchInput && e.target !== dropdown) {
    dropdown.style.display = 'none';
  }
});
</script>

<style>
.project-dropdown {
  position: absolute;
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 8px;
  margin-top: 5px;
  max-height: 300px;
  overflow-y: auto;
  width: calc(100% - 2px);
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.dropdown-item {
  padding: 12px 15px;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background-color: rgba(56, 189, 248, 0.1);
}

.form-group {
  position: relative;
}
</style>

</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>
