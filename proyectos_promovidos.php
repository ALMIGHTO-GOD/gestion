<?php
// --- 1. DEFINIR QUÉ PÁGINA ES ---
$pagina_actual = 'promovidos';

// --- 2. INCLUIR EL ENCABEZADO ---
require_once 'header.php';

// Si el header tuvo un error de BD, lo mostramos
if (!empty($error_bd)) {
    die("<main class='main-content'><p style='color: red;'>$error_bd</p></main></body></html>");
}

// --- 3. CONSULTA SQL PARA PROYECTOS PROMOVIDOS CON DESCRIPCIÓN ---
$sql_promovidos = "SELECT 
                    he.id_historia_exito,
                    he.url_multimedia,
                    he.tipo_multimedia,
                    p.id_proyecto,
                    p.nombre_proyecto,
                    p.descripcion_breve,
                    u.nombre_completo AS autor
                  FROM historias_exito AS he
                  JOIN Proyectos AS p ON he.id_proyecto = p.id_proyecto
                  JOIN Usuarios AS u ON p.id_usuario = u.id_usuario
                  WHERE he.tipo_multimedia IN ('enlace_youtube', 'enlace_vimeo', 'mp4', 'mp3')
                  ORDER BY he.id_historia_exito DESC";

$resultado_promovidos = $conn->query($sql_promovidos);
$conteo_promovidos = 0;
if ($resultado_promovidos) {
    $conteo_promovidos = $resultado_promovidos->num_rows;
}
?>

<title>Proyectos Promovidos - MEDIA SPROUTS</title>
<link rel="stylesheet" href="css/floating_button.css" />

<main class="main-content">
  <div class="projects-section__header">
    <h1>Proyectos Promovidos</h1>
    <p>Galería de proyectos destacados con contenido multimedia.</p>
  </div>

  <div class="promoted-list">
    <?php
    if ($conteo_promovidos > 0):
        while($promovido = $resultado_promovidos->fetch_assoc()):
            $video_url = $promovido['url_multimedia'];
            $tipo = $promovido['tipo_multimedia'];
            $video_id = '';
            $thumbnail_url = '';
            $embed_url = '';
            
            // Procesar según el tipo de multimedia
            if ($tipo == 'enlace_youtube') {
                // Extraer ID de YouTube
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video_url, $matches)) {
                    $video_id = $matches[1];
                }
                $thumbnail_url = $video_id ? "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg" : "https://via.placeholder.com/400x225/1e293b/38bdf8?text=YouTube";
                $embed_url = $video_id ? "https://www.youtube.com/embed/{$video_id}" : $video_url;
                
            } elseif ($tipo == 'enlace_vimeo') {
                // Extraer ID de Vimeo
                if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                    $video_id = $matches[1];
                }
                $thumbnail_url = "https://via.placeholder.com/400x225/1e293b/38bdf8?text=Vimeo";
                $embed_url = $video_id ? "https://player.vimeo.com/video/{$video_id}" : $video_url;
                
            } elseif ($tipo == 'mp4') {
                // Archivo MP4 local
                $thumbnail_url = "https://via.placeholder.com/400x225/1e293b/38bdf8?text=MP4+Video";
                $embed_url = $video_url; // Ruta local del archivo
                
            } elseif ($tipo == 'mp3') {
                // Archivo MP3 local
                $thumbnail_url = "https://via.placeholder.com/400x225/1e293b/38bdf8?text=MP3+Audio";
                $embed_url = $video_url; // Ruta local del archivo
            }
    ?>
        <article class="promoted-card">
          <div class="promoted-card__thumbnail">
            <img src="<?php echo $thumbnail_url; ?>" alt="<?php echo htmlspecialchars($promovido['nombre_proyecto']); ?>">
            <div class="play-overlay" onclick="openVideoModal('<?php echo $embed_url; ?>', '<?php echo htmlspecialchars($promovido['nombre_proyecto']); ?>', '<?php echo $tipo; ?>')">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="white">
                <path d="M8 5v14l11-7z"/>
              </svg>
            </div>
          </div>
          
          <div class="promoted-card__content">
            <h3><?php echo htmlspecialchars($promovido['nombre_proyecto']); ?></h3>
            <p class="author">Por: <?php echo htmlspecialchars($promovido['autor']); ?></p>
            <p class="description"><?php echo htmlspecialchars($promovido['descripcion_breve']); ?></p>
            
            <div class="promoted-card__actions">
              <?php if ($tipo == 'enlace_youtube'): ?>
              <a href="<?php echo htmlspecialchars($video_url); ?>" target="_blank" class="btn-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 5px;">
                  <path d="M14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3m-2 16H5V5h7V3H5c-1.11 0-2 .89-2 2v14c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2v-7h-2v7z"/>
                </svg>
                Ver en YouTube
              </a>
              <?php endif; ?>
              
              <button onclick="openVideoModal('<?php echo $embed_url; ?>', '<?php echo htmlspecialchars($promovido['nombre_proyecto']); ?>', '<?php echo $tipo; ?>')" class="btn-play">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 5px;">
                  <path d="M8 5v14l11-7z"/>
                </svg>
                Reproducir Aquí
              </button>
            </div>
          </div>
        </article>
    <?php
        endwhile;
    else:
    ?>
        <p style="color: white; text-align: center; padding: 40px;">No hay proyectos promovidos aún.</p>
    <?php
    endif;
    $resultado_promovidos->close();
    ?>
  </div>

  <!-- Botón flotante para nueva publicación (solo admin) -->
  <?php if ($rol_usuario == 'admin'): ?>
  <a href="subir_promovido.php" class="floating-btn">
    + Nueva Publicación
  </a>
  <?php endif; ?>
</main>

<!-- Modal para reproducir video -->
<div id="videoModal" class="video-modal" onclick="closeVideoModal()">
  <div class="video-modal__content" onclick="event.stopPropagation()">
    <button class="video-modal__close" onclick="closeVideoModal()">&times;</button>
    <h3 id="videoTitle"></h3>
    <div class="video-container">
      <iframe id="videoFrame" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
  </div>
</div>

<style>
/* Promoted Cards - Horizontal Layout */
.promoted-list {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.promoted-card {
  display: flex;
  background-color: var(--bg-card);
  border-radius: 12px;
  border: 1px solid var(--border);
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
  height: 220px;
}

.promoted-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.promoted-card__thumbnail {
  position: relative;
  width: 380px;
  min-width: 380px;
  overflow: hidden;
  cursor: pointer;
}

.promoted-card__thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.play-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.4);
  opacity: 0;
  transition: opacity 0.3s;
}

.promoted-card__thumbnail:hover .play-overlay {
  opacity: 1;
}

.promoted-card__content {
  flex: 1;
  padding: 20px 25px;
  display: flex;
  flex-direction: column;
}

.promoted-card__content h3 {
  font-size: 1.5rem;
  color: #ffffff;
  margin: 0 0 8px 0;
  padding-right: 0;
}

.promoted-card__content .author {
  color: var(--text-muted);
  font-size: 0.9rem;
  margin: 0 0 12px 0;
}

.promoted-card__content .description {
  color: var(--text-main);
  line-height: 1.6;
  margin: 0 0 auto 0;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.promoted-card__actions {
  display: flex;
  gap: 15px;
  margin-top: 15px;
}

.btn-link, .btn-play {
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  border: none;
  display: inline-flex;
  align-items: center;
}

.btn-link {
  background-color: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-link:hover {
  background-color: rgba(56, 189, 248, 0.1);
}

.btn-play {
  background-color: var(--primary);
  color: var(--bg-body);
}

.btn-play:hover {
  background-color: var(--primary-hover);
}

/* Video Modal */
.video-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}

.video-modal.active {
  display: flex;
}

.video-modal__content {
  background-color: var(--bg-card);
  border-radius: 12px;
  padding: 25px;
  max-width: 900px;
  width: 90%;
  position: relative;
}

.video-modal__close {
  position: absolute;
  top: 10px;
  right: 15px;
  background: none;
  border: none;
  color: var(--text-main);
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  padding: 5px 10px;
}

.video-modal__close:hover {
  color: var(--primary);
}

.video-modal__content h3 {
  color: #ffffff;
  margin: 0 0 20px 0;
  padding-right: 40px;
}

.video-container {
  position: relative;
  padding-bottom: 56.25%; /* 16:9 aspect ratio */
  height: 0;
  overflow: hidden;
}

.video-container iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>

<script>
function openVideoModal(videoUrl, title, tipo) {
  const modal = document.getElementById('videoModal');
  const videoContainer = document.querySelector('.video-container');
  const videoTitle = document.getElementById('videoTitle');
  
  videoTitle.textContent = title;
  
  // Limpiar contenido anterior
  videoContainer.innerHTML = '';
  
  // Crear elemento según el tipo
  if (tipo === 'enlace_youtube' || tipo === 'enlace_vimeo') {
    // Usar iframe para YouTube y Vimeo
    const iframe = document.createElement('iframe');
    iframe.id = 'videoFrame';
    iframe.src = videoUrl + '?autoplay=1';
    iframe.frameBorder = '0';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    videoContainer.appendChild(iframe);
    
  } else if (tipo === 'mp4') {
    // Usar tag video para MP4
    const video = document.createElement('video');
    video.controls = true;
    video.autoplay = true;
    video.style.width = '100%';
    video.style.height = '100%';
    video.style.position = 'absolute'; // IMPORTANTE: Para llenar el contenedor responsivo
    video.style.top = '0';
    video.style.left = '0';
    video.src = videoUrl;
    videoContainer.appendChild(video);
    
  } else if (tipo === 'mp3') {
    // Usar tag audio para MP3
    videoContainer.style.paddingBottom = '0';
    videoContainer.style.height = 'auto';
    const audio = document.createElement('audio');
    audio.controls = true;
    audio.autoplay = true;
    audio.style.width = '100%';
    audio.src = videoUrl;
    videoContainer.appendChild(audio);
  }
  
  modal.classList.add('active');
}

function closeVideoModal() {
  const modal = document.getElementById('videoModal');
  const videoContainer = document.querySelector('.video-container');
  
  // Limpiar todo el contenido (detiene reproducción)
  videoContainer.innerHTML = '';
  // Restaurar padding para próximos videos
  videoContainer.style.paddingBottom = '56.25%';
  videoContainer.style.height = '0';
  
  modal.classList.remove('active');
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeVideoModal();
  }
});
</script>

</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>
