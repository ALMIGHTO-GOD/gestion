// Este objeto está perfecto
const reglasPassword = {
  minLength: 8,
  maxLength: 12,
  requiereLetras: true,
  requiereNumeros: true,
  noEspacios: true,
  noEspeciales: true,
};

function validarFormulario() {
  
  // Agarramos los valores
  const nombre = document.getElementById("nombre").value.trim();
  const correo = document.getElementById("correo").value.trim();
  const telefono = document.getElementById("telefono").value.trim();
  const password = document.getElementById("contraseña1").value.trim();
  const confirmar = document.getElementById("contraseña2").value.trim();
  const errorDiv = document.getElementById("errorMsg");
  errorDiv.textContent = ""; // Limpiamos errores viejos

  // --- 1. ¡NUEVO! VALIDACIÓN DE CAMPOS VACÍOS ---
  // (La que te sugerí antes)
  if (nombre === "" || telefono === "" || correo === "" || password === "") {
      errorDiv.textContent = "Error: Todos los campos son obligatorios.";
      return false; // ¡FRENA EL ENVÍO!
  }

  // --- 2. VALIDACIÓN DE CORREO ---
  const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!regexCorreo.test(correo)) {
    errorDiv.textContent = "Por favor, ingresa un correo electrónico válido (ejemplo: usuario@dominio.com)";
    return false; // Frenamos
  }

  // --- 3. VALIDACIONES DE CONTRASEÑA ---
  if (
    password.length < reglasPassword.minLength ||
    password.length > reglasPassword.maxLength
  ) {
    errorDiv.textContent = `La contraseña debe tener entre ${reglasPassword.minLength} y ${reglasPassword.maxLength} caracteres.`;
    return false; // Frenamos
  }
  if (reglasPassword.requiereLetras && !/[a-zA-Z]/.test(password)) {
    errorDiv.textContent = "La contraseña debe contener al menos una letra.";
    return false; // Frenamos
  }
  if (reglasPassword.requiereNumeros && !/\d/.test(password)) {
    errorDiv.textContent = "La contraseña debe contener al menos un número.";
    return false; // Frenamos
  }
  if (reglasPassword.noEspacios && /\s/.test(password)) {
    errorDiv.textContent = "La contraseña no puede contener espacios.";
    return false; // Frenamos
  }
  if (reglasPassword.noEspeciales && /[^a-zA-Z0-9]/.test(password)) {
    errorDiv.textContent = "La contraseña no puede contener caracteres especiales ni emojis.";
    return false; // Frenamos
  }
  if (password !== confirmar) {
    errorDiv.textContent = "Las contraseñas no coinciden.";
    return false; // Frenamos
  }

  // --- 4. ¡EL ARREGLO! ---
  // Borramos la línea "window.location.href"
  
  // (Opcional: puedes dejar este alert si quieres)
  // alert("Formulario validado, enviando al servidor..."); 

  // --- 5. ¡LUZ VERDE! ---
  // Si llegó hasta aquí, todo está bien.
  // Devolvemos "true" para que el <form> SÍ se envíe a "register.php"
  return true; 
}