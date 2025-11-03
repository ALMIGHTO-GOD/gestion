import mysql from "mysql2";

const conexion = mysql.createConnection({
  host: "localhost",
  user: "root", // usuario por defecto en XAMPP
  password: "", // déjalo vacío si no tienes contraseña
  database: "usuariosdb",
});

conexion.connect(error => {
  if (error) {
    console.error("Error al conectar con MySQL:", error);
  } else {
    console.log("Conectado a MySQL");
  }
});

export default conexion;
