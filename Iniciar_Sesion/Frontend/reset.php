<?php
// Primero, nos aseguramos de que el token venga en la URL
if (!isset($_GET['token'])) {
    die("Token no proporcionado. Enlace inválido.");
}
$token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña - MEDIA SPROUTS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main>
        <div class="login-container">
            <h2>Crear nueva contraseña</h2>
            
            <form action="actualizar_pass.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="nueva_pass" required>
                </div>
                
                <div class="form-group">
                    <label for="password2">Confirmar Contraseña</label>
                    <input type="password" id="password2" name="confirmar_pass" required>
                </div>
                
                <button type="submit" class="btn-login">Guardar Contraseña</button>
            </form>
        </div>
    </main>
</body>
</html>