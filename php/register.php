<?php
// Usaremos PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carga los archivos de PHPMailer (asumimos que están en una carpeta 'phpmailer')
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// --- 1. CONEXIÓN A LA BASE DE DATOS ---
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3307;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }

// --- 2. OBTENER DATOS DEL FORMULARIO ---
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$password_plano = $_POST['password'];

// --- 3. VALIDAR SI EL CORREO YA EXISTE ---
$sql_check = "SELECT id_usuario FROM Usuarios WHERE correo = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();

if ($resultado_check->num_rows > 0) {
    die("Error: Ese correo electrónico ya está registrado. <a href='login.html'>Intenta iniciar sesión</a>.");
}

// --- 4. HASHEAR LA CONTRASEÑA ---
$pass_hash = password_hash($password_plano, PASSWORD_DEFAULT);

// --- 5. INSERTAR EL NUEVO USUARIO ---
$sql_insert = "INSERT INTO Usuarios (nombre_completo, correo, telefono, contrasena, rol) VALUES (?, ?, ?, ?, 'usuario')";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ssss", $nombre, $email, $telefono, $pass_hash);

if ($stmt_insert->execute()) {
    // --- 6. ¡ENVIAR CORREO DE BIENVENIDA! ---
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'luedhernandezor@ittepic.edu.mx'; // TU CORREO DE GMAIL
        $mail->Password   = 'neiwyvabfsnrvwun';    // TU CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('luedhernandezor@ittepic.edu.mx', 'MEDIA SPROUTS');
        $mail->addAddress($email, $nombre); // Añade al nuevo usuario

        $mail->isHTML(true);
        $mail->Subject = '¡Bienvenido a MEDIA SPROUTS!';
        $mail->Body    = "Hola $nombre,<br><br>¡Gracias por registrarte en nuestra plataforma!<br>Ya puedes iniciar sesión y empezar a subir tus proyectos.<br><br>Saludos,<br>El equipo de MEDIA SPROUTS";

        $mail->send();
        echo "¡Registro exitoso! Te hemos enviado un correo de bienvenida. Ya puedes <a href='login.html'>iniciar sesión</a>.";
    
    } catch (Exception $e) {
        // Si el registro funciona pero el correo falla
        echo "¡Registro exitoso! No pudimos enviar el correo de bienvenida. Error: {$mail->ErrorInfo}. Ya puedes <a href='login.html'>iniciar sesión</a>.";
    }

} else {
    echo "Error al registrar el usuario: " . $conn->error;
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>