// Este objeto está perfecto
const reglasPassword = {
  minLength: 8,
  maxLength: 12,
  requiereLetras: true,
  requiereNumeros: true,
  noEspacios: true,
  noEspeciales: true,
};

// --- CAMBIO 1: Quitamos "async" ---
// Esta función SÍ debe devolver true o false
function validarFormulario() {
  // Todo esto está perfecto
  const nombre = document.getElementById("nombre").value.trim();
  const correo = document.getElementById("correo").value.trim();
  const telefono = document.getElementById("telefono").value.trim();
  const password = document.getElementById("contraseña1").value.trim();
  const confirmar = document.getElementById("contraseña2").value.trim();
  const errorDiv = document.getElementById("errorMsg");
  errorDiv.textContent = "";

  //Validación del correo electrónico (perfecto)
  const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!regexCorreo.test(correo)) {
    errorDiv.textContent =
      "Por favor, ingresa un correo electrónico válido (ejemplo: usuario@dominio.com)";

    // --- CAMBIO 2: Devolvemos "false" para FRENAR el formulario ---
    return false;
  }

  // Validaciones basadas en el JSON (perfecto)
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
    errorDiv.textContent =
      "La contraseña no puede contener caracteres especiales ni emojis.";
    return false; // Frenamos
  }

  if (password !== confirmar) {
    errorDiv.textContent = "Las contraseñas no coinciden.";
    return false; // Frenamos
  }

  // --- CAMBIO 3: BORRAMOS TODO EL CÓDIGO de "fetch" ---
  // Ya no lo necesitamos, porque el formulario se enviará solo
  // a "register.php" gracias al HTML.

  // --- CAMBIO 4: Damos "luz verde" ---
  // Si llegó hasta aquí, todas las validaciones pasaron
  alert("Formulario validado, enviando al servidor..."); // Opcional, para que veas que sí pasa
  // --- Redirigir a login.html ---
  window.location.href = "login.html";

  return true; // Le decimos al HTML que SÍ puede enviar el formulario
}
