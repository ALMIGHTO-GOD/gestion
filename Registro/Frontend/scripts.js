    const reglasPassword = {
      "minLength": 8,
      "maxLength": 12,
      "requiereLetras": true,
      "requiereNumeros": true,
      "noEspacios": true,
      "noEspeciales": true
    };

    // 🔍 Función de validación
    function validarFormulario() {
      const password = document.getElementById('contraseña1').value.trim();
      const confirmar = document.getElementById('contraseña2').value.trim();
      const errorDiv = document.getElementById('errorMsg');
      errorDiv.textContent = '';

      // Validaciones basadas en el JSON
      if (password.length < reglasPassword.minLength || password.length > reglasPassword.maxLength) {
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
        errorDiv.textContent = "La contraseña no puede contener caracteres especiales ni emojis.";
        return;
      }

      if (password !== confirmar) {
        errorDiv.textContent = "Las contraseñas no coinciden.";
        return;
      }

      // ✅ Si todo está bien:
      alert("¡Registro exitoso!");
      document.getElementById('registroForm').reset();
    }