const reglasPassword = {
  minLength: 8,
  maxLength: 12,
  requiereLetras: true,
  requiereNumeros: true,
  noEspacios: true,
  noEspeciales: true,
};

//Función de validación
async function validarFormulario() {
  const correo = document.getElementById("correo").value.trim();
  const password = document.getElementById("contraseña1").value.trim();
  const confirmar = document.getElementById("contraseña2").value.trim();
  const errorDiv = document.getElementById("errorMsg");
  errorDiv.textContent = "";

  //Validación del correo electrónico
  const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!regexCorreo.test(correo)) {
    errorDiv.textContent =
      "Por favor, ingresa un correo electrónico válido (ejemplo: usuario@dominio.com)";
    return;
  }

  // Validaciones basadas en el JSON
  if (
    password.length < reglasPassword.minLength ||
    password.length > reglasPassword.maxLength
  ) {
    errorDiv.textContent = `La contraseña debe tener entre ${reglasPassword.minLength} y ${reglasPassword.maxLength} caracteres.`;
    return;
  }

  if (reglasPassword.requiereLetras && !/[a-zA-Z]/.test(password)) {
    errorDiv.textContent = "La contraseña debe contener al menos una letra.";
    return;
  }

  if (reglasPassword.requiereNumeros && !/\d/.test(password)) {
    errorDiv.textContent = "La contraseña debe contener al menos un número.";
    return;
  }

  if (reglasPassword.noEspacios && /\s/.test(password)) {
    errorDiv.textContent = "La contraseña no puede contener espacios.";
    return;
  }

  if (reglasPassword.noEspeciales && /[^a-zA-Z0-9]/.test(password)) {
    errorDiv.textContent =
      "La contraseña no puede contener caracteres especiales ni emojis.";
    return;
  }

  if (password !== confirmar) {
    errorDiv.textContent = "Las contraseñas no coinciden.";
    return;
  }

  // Si pasa todas las validaciones, enviamos al backend:
  const usuario = {
    nombre,
    apellidos,
    telefono,
    correo,
    contraseña: password,
  };

  try {
    const respuesta = await fetch(
      "http://localhost:4000/api/usuarios/registro",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(usuario),
      }
    );

    const data = await respuesta.json();

    if (respuesta.ok) {
      alert("¡Registro exitoso! Usuario guardado en la base de datos.");
      document.getElementById("registroForm").reset();
    } else {
      errorDiv.textContent =
        data.error || "Ocurrió un error al registrar el usuario.";
    }
  } catch (error) {
    console.error("Error en el registro:", error);
    errorDiv.textContent = "No se pudo conectar con el servidor.";
  }
}
