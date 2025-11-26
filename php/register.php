<?php
// (Aquí va tu código de PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../lib/phpmailer/PHPMailer-master/src/Exception.php';
require '../lib/phpmailer/PHPMailer-master/src/PHPMailer.php';
require '../lib/phpmailer/PHPMailer-master/src/SMTP.php';

// --- ¡NUEVO GUARDIÁN DE SEGURIDAD! ---
if (
    empty(trim($_POST['nombre'])) ||
    empty(trim($_POST['email'])) ||
    empty(trim($_POST['telefono'])) ||
    empty(trim($_POST['password']))
) {
    header("Location: ../registro.html?error=empty_fields");
    exit();
}
// --- FIN DEL NUEVO GUARDIÁN ---


// --- 1. CONEXIÓN A LA BASE DE DATOS ---
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3306;

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) { 
    header("Location: ../login.html?error=db_connection_failed");
    exit();
}

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
    header("Location: ../registro.html?error=email_exists");
    exit();
}
$stmt_check->close();

// --- 4. HASHEAR LA CONTRASEÑA ---
$pass_hash = password_hash($password_plano, PASSWORD_DEFAULT);

// --- 5. ¡INICIAMOS LA TRANSACCIÓN! ---
// A partir de aquí, o todo funciona, o nada se guarda.
$conn->begin_transaction();

try {
    // --- 6. INSERTAR EL NUEVO USUARIO (Temporalmente) ---
    $sql_insert = "INSERT INTO Usuarios (nombre_completo, correo, telefono, contrasena, rol) VALUES (?, ?, ?, ?, 'usuario')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $nombre, $email, $telefono, $pass_hash);
    
    // Si la inserción falla, lanzamos un error para ir al 'catch'
    if (!$stmt_insert->execute()) {
        throw new Exception("Error al guardar en la base de datos.");
    }
    $stmt_insert->close();

    // --- 7. INTENTAR ENVIAR EL CORREO DE BIENVENIDA ---
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'luedhernandezor@ittepic.edu.mx';
    $mail->Password   = 'neiwyvabfsnrvwun';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('luedhernandezor@ittepic.edu.mx', 'MEDIA SPROUTS');
    $mail->addAddress($email); 

    $mail->isHTML(true);
    $mail_subject = '¡Bienvenido a MEDIA SPROUTS!';
    $mail_body    = "Hola $nombre,<br><br>¡Gracias por registrarte en nuestra plataforma!<br>Ya puedes iniciar sesión y empezar a subir tus proyectos.<br><br>Saludos,<br>El equipo de MEDIA SPROUTS";
    
    $mail->Subject = $mail_subject;
    $mail->Body    = $mail_body;
    
    $mail->send();

    // --- 8. ¡ÉXITO TOTAL! ---
    $conn->commit();
    
    // Redirigir al login automáticamente
    header("Location: ../login.html?registro=exitoso");
    exit();

} catch (Exception $e) {
    // --- 9. ¡ALGO FALLÓ! ---
    $conn->rollback();

    $error_info = isset($mail) ? $mail->ErrorInfo : $e->getMessage();

    // Si es error de BD (el que lanzamos manualmente o nativo)
    if ($e->getMessage() == "Error al guardar en la base de datos." || str_contains($e->getMessage(), "INSERT")) {
        header("Location: ../registro.html?error=db_insert_failed");
        exit();
    }
    
    // Si es error de conexión/internet (SMTP)
    if (str_contains($error_info, "SMTP connect() failed") || str_contains($error_info, "Could not authenticate") || str_contains($error_info, "connect to")) {
        header("Location: ../login.html?error=connection_failed");
        exit();
    } 
    
    // Cualquier otro error
    header("Location: ../login.html?error=unknown_error");
    exit();
}

$conn->close();
?>