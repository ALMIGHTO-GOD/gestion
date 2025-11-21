<?php
// ¡Paso 1: Siempre iniciar la sesión!
session_start();

// --- CONEXIÓN A LA BASE DE DATOS ---
$servidor = "127.0.0.1";
$usuario_db = "root"; 
$pass_db = "";        
$db_nombre = "media_sprouts";
$puerto = 3306; // El puerto que configuraste

$conn = new mysqli($servidor, $usuario_db, $pass_db, $db_nombre, $puerto);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// --- OBTENER DATOS DEL FORMULARIO ---
$email = $_POST['email'];
$password_plano = $_POST['password'];

// --- BUSCAR AL USUARIO POR CORREO ---
$sql = "SELECT id_usuario, nombre_completo, contrasena, rol FROM Usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    // Usuario sí existe, ahora verificamos la contraseña
    $usuario = $resultado->fetch_assoc();
    
    // --- ¡LA FUNCIÓN MÁGICA! ---
    // Compara la contraseña del formulario (plana) con el HASH de la BD
    if (password_verify($password_plano, $usuario['contrasena'])) {
        
        // ¡Contraseña correcta!
        
        // Guardamos los datos del usuario en la "mochila" de la sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_completo'];
        $_SESSION['rol_usuario'] = $usuario['rol'];
        
        // Redirigir al panel principal (el dashboard)
        header("Location: ../dashboard.php");
        exit(); // Asegura que el script se detenga
        
    } else {
        // Contraseña incorrecta
        die("Error: Contraseña incorrecta. <a href='../login.html'>Inténtalo de nuevo</a>.");
    }
} else {
    // Usuario no encontrado
    die("Error: Usuario no encontrado. <a href='../registro.html'>Regístrate aquí</a>.");
}

$stmt->close();
$conn->close();
?>