<?php
// --- 1. CONEXIÓN A LA BASE DE DATOS ---
// (¡Acuérdate de poner el puerto 3307!)
$servidor = "127.0.0.1";
$usuario_db = "root"; // Usuario por defecto en XAMPP
$pass_db = "";        // Password por defecto en XAMPP
$db_nombre = "media_sprouts";
$puerto = 3307;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- 2. VALIDAR EL CORREO ---
if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Buscamos si el correo existe en la tabla Usuarios
    $sql = "SELECT id_usuario FROM Usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // --- 3. SI EXISTE, GENERAMOS EL TOKEN ---
        $token = bin2hex(random_bytes(32)); // Genera un token seguro
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour')); // El token vence en 1 hora

        // Guardamos el token y la fecha de expiración en la BD
        $sql_update = "UPDATE Usuarios SET reset_token = ?, reset_expira = ? WHERE correo = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sss", $token, $expira, $email);
        $stmt_update->execute();

        // --- 4. ENVIAR EL CORREO (EL GRAN RETO) ---
        // Crear el enlace que irá en el correo
        $enlace_reset = "http://localhost/tu-proyecto/reset.php?token=" . $token;

        $asunto = "Recuperar tu contraseña de MEDIA SPROUTS";
        $mensaje = "Hola, haz clic en este enlace para restablecer tu contraseña: \n\n" . $enlace_reset;
        $headers = "From: no-reply@mediasprouts.com";

        // *** ¡OJO AQUÍ, COMPA! ***
        // La función mail() de PHP casi NUNCA funciona bien en XAMPP
        // porque no tienes un servidor de correos instalado.
        // Para que esto jale, tendrás que usar una librería como PHPMailer
        // que te deja usar tu cuenta de Gmail o Outlook para mandar los correos.
        
        // mail($email, $asunto, $mensaje, $headers); // Esto probablemente fallará

        echo "Si tu correo existe, te hemos enviado un enlace. (Por ahora, revisa este enlace: " . $enlace_reset . ")";
        // Por ahora, mostramos el enlace en pantalla para que puedas probar.
    } else {
        echo "Si tu correo existe, te hemos enviado un enlace.";
        // (Siempre damos el mismo mensaje por seguridad)
    }
}
$conn->close();
?>