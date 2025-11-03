import express from "express";
import cors from "cors";
import bodyParser from "body-parser";
import conexion from "./db.js";

const app = express();
app.use(cors());
app.use(bodyParser.json());
app.use(express.static("public")); // sirve tu HTML

// Ruta para registrar usuario
app.post("/api/registro", (req, res) => {
  const { nombre, telefono, correo, contraseña, rol, fechaRegistro } = req.body;

  const sql = `INSERT INTO usuarios (nombre, telefono, correo, contraseña, rol, fechaRegistro)
               VALUES (?, ?, ?, ?, ?, ?)`;

  conexion.query(
    sql,
    [nombre, telefono, correo, contraseña, rol, new Date(fechaRegistro)],
    (error, resultado) => {
      if (error) {
        console.error("❌ Error al guardar:", error);
        res.status(500).json({ mensaje: "Error al registrar el usuario" });
      } else {
        res.json({ mensaje: "✅ Usuario registrado correctamente" });
      }
    }
  );
});

const PORT = 3000;
app.listen(PORT, () => {
  console.log(`🚀 Servidor ejecutándose en http://localhost:${PORT}`);
});
