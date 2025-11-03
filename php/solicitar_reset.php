<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// --- 2. VALIDAR EL CORREO ---
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $sql = "SELECT id_usuario FROM Usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // --- 3. SI EXISTE, GENERAMOS EL TOKEN ---
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        $sql_update = "UPDATE Usuarios SET reset_token = ?, reset_expira = ? WHERE correo = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sss", $token, $expira, $email);
        $stmt_update->execute();

        // --- 4. ENVIAR EL CORREO (CON PHPMailer) ---
        $enlace_reset = "http://localhost/media_sprouts/reset.php?token=" . $token;

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
            $mail->addAddress($email); 

            $mail->isHTML(true);
            $mail->Subject = 'Enlace para recuperar tu contraseña';
            $mail->Body    = "Hola,<br><br>Haz clic en este enlace para restablecer tu contraseña:<br><br><a href='$enlace_reset'>$enlace_reset</a><br><br>Si no solicitaste esto, ignora este correo.";

            $mail->send();
            echo "Si tu correo existe, te hemos enviado un enlace de recuperación, puedes cerrar esta ventana.";
        
        } catch (Exception $e) {
            echo "Si tu correo existe, te hemos enviado un enlace, Puedes cerrar esta ventana. (Error al enviar: {$mail->ErrorInfo})";
        }

    } else {
        echo "Si tu correo existe, te hemos enviado un enlace. De lo contrario regresa a <a href='registro.html'>Regístrate aquí</a>";
    }
}
$conn->close();
?>