<?php
// -------------------- CONFIGS ----------------------------

// Configuraciones del proyeto

define("CHARSET", "UFT-8");
header("Content-Type: text/html; charset=UTF-8;");
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("UTC");
date_default_timezone_set("America/Guatemala");
session_start();

$fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");

// ------------------- CONEXION SQL-------------------------

$con = mysqli_connect("localhost", "root", "");
if (!$con) {
    die("Error DB connect " . mysqli_connect_error());
}

mysqli_select_db($con, "usuarios2024");
$con->set_charset("utf8");

// -------------------------- PETICIONES HTTP -------------------- //

// -------------------------- PETICION GET ---------------------

// -------------------------- [GET] LISTA DE USUARIOS ALL ------------------- //

// Se inicializa un nuevo Metodo HTTP [GET] donde con "==", se hace referencia al Metodo estricto
// estricto GET

// Obtener los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Se crea una nueva peticion [GET]
    $quest = isset($_GET["quest"]) ? $_GET["quest"] : null;
    // Se inicializa la variable $quest que guarda la funcion de "lista_usuarios"
    if ($quest == "lista_usuarios") {
        // Consulta a MYSQL
        $mysql = "SELECT * FROM usuarios;";
        // $result guarda y REALIZA la consulta SQL [$mysql]
        $result = mysqli_query($con, $mysql);
        // Ciclo IF donde condiciona a $result para traer los datos de DB en forma de array().
        if ($result) {
            $data = [];
            // Ciclo While que asocia y carga las filas con la informacion de la DB.
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            // Devuelve la consulta en formato JSON.
            echo json_encode($data);
        } else {
            // Si no es posible lo escrito en el if, condiciona a un error Not Found
            header("HTTP/1.1 404 Not Found");
            echo json_encode(["error" => "No se encontraron datos"]);
        }
    }
}

// ----------------------- [GET] LISTA USUARIOS FILTRO ----------------------- //
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Se crea una nueva peticion [GET]
    $quest = isset($_GET["quest"]) ? $_GET["quest"] : null;
    if ($quest == "lista_usuarios_filtro") {
        $letra = isset($_GET["letra"]) ? $_GET["letra"] : "";
        $usuario = isset($_GET["usuario"]) ? $_GET["usuario"] : "";
        $contrasena = isset($_GET["contrasena"]) ? $_GET["contrasena"] : "";

        // Consulta a MySQL para buscar los usuarios que coincidan con la búsqueda
        $mysql = "SELECT * FROM usuarios WHERE (id like '%$letra%' OR nombre like '%$letra%' OR usuario like '%$letra%' OR contrasena like '%$letra%') AND usuario = '$usuario'";
        $result = mysqli_query($con, $mysql);

        if ($result && mysqli_num_rows($result) > 0) {
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $db_password = $row["contrasena"];

                // Si es un hash, usar password_verify
                if (password_verify($contrasena, $db_password)) {
                    $data = $row; // Contraseña correcta
                    break; 
                } elseif ($contrasena === $db_password) {
                    $data = $row;
                    break; 
                }
            }

            if (!empty($data)) {
                // Retornar datos del usuario
                echo json_encode($data);
            } else {
                // Si no hay coincidencias de usuario y contraseña
                header("HTTP/1.1 401 Unauthorized");
                echo json_encode([
                    "error" => "Usuario o contraseña incorrectos",
                ]);
            }
        } else {
            // Si no se encuentra ningún usuario que coincida con la búsqueda
            header("HTTP/1.1 404 Not Found");
            echo json_encode(["error" => "Usuario no encontrado"]);
        }
    }
}


// ------------------------------- [POST] AGREGAR UN NUEVO USUARIO ------------------------------------ //

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Decodifica los datosde JSON recibidos
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica si el quest es para ingresar un usuario
    if ($data["quest"] == "ingresar_usuario") {
        // Asignacion de variables
        $nombre = isset($data["nombre"])
            ? $con->real_escape_string($data["nombre"])
            : "";
        $usuario = isset($data["usuario"])
            ? $con->real_escape_string($data["usuario"])
            : "";
        $contrasena = isset($data["contrasena"])
            ? $con->real_escape_string($data["contrasena"])
            : "";

        // Encriptar contrasena en DB.
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Preparacion de consulta para inyectar informacion en la DB.

        $mysql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$usuario', '$hashed_password')";

        // Consulta a DB
        $result = mysqli_query($con, $mysql);

        // Obtener el ID que genero INSERT INTO
        $id_generado = mysqli_insert_id($con);

        // Creando nuevo arreglo con los datos insertados.

        $response = [
            "success" => true,
            "message" => "Usuario creado exitosamente",
            "id" => $id_generado,
            "nombre" => $nombre,
            "usuario" => $usuario,
            "contrasena" => $hashed_password,
        ];

        // Devolver la respuesta en JSON

        echo json_encode($response);
    }
}

// ------------------------------- [PUT] EDITAR UN USUARIO ------------------------------------ //

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["quest"]) && $data["quest"] == "editar_usuario") {
        $id = isset($data["id"]) ? $con->real_escape_string($data["id"]) : "";
        $nombre = isset($data["nombre"]) ? $con->real_escape_string($data["nombre"]) : "";
        $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
        $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";

        $sql = "UPDATE usuarios SET 
                nombre = '$nombre', 
                usuario = '$usuario', 
                contrasena = '$contrasena' 
                WHERE id = '$id'";

        $result = mysqli_query($con, $sql);

        if ($result) {
            $response = [
                "success" => true,
                "message" => "Usuario actualizado exitosamente",
                "id" => $id,
                "nombre" => $nombre,
                "usuario" => $usuario
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "Error al actualizar el usuario: " . mysqli_error($con)
            ];
        }

        // Devolver la respuesta en JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

// ------------------------- [DELETE] ELIMINAR UN USUARIO ---------------------- //

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["quest"]) && $data["quest"] == "eliminar_usuario") {
        $id = isset($data["id"]) ? $con->real_escape_string($data["id"]) : "";

        if (!empty($id)) {
            $sql = "DELETE FROM usuarios WHERE id = '$id'";

            $result = mysqli_query($con, $sql);

            if ($result) {
                $response = [
                    "success" => true,
                    "message" => "Usuario eliminado exitosamente",
                    "id" => $id
                ];
            } else {
                $response = [
                    "success" => false,
                    "message" => "Error al eliminar el usuario: " . mysqli_error($con)
                ];
            }
        } else {
            $response = [
                "success" => false,
                "message" => "ID de usuario no proporcionado"
            ];
        }

        // Devolver la respuesta en JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}







// -------------------------- PETICION GET ---------------------

// Selecciona todos los campos de la Tabla Usuarios
// $sql = "SELECT * FROM usuarios";

// Realiza la consulta a la db argumentando la conexion y la consula $sql
//$result = mysqli_query($con, $sql);

// Se obtienen las filas de la tabla Usuarios solo si es mayor a 0, de lo contrario imprime 0 results.
//if (mysqli_num_rows($result) > 0) {
// Ciclo While para obtener la tabla Usuarios como un Array.
//while($request = mysqli_fetch_assoc($result)) {
// Imprime los campos de los usuarios exceptuando la contrasena por seguridad.
//echo "| " . $request["id"]. " | " . "| ". $request["nombre"]. " | ".  " | " . $request["usuario"] . " | " . "\n" ;
//  }
//} else {
//echo "0 results";
//}

// $request = mysqli_num_rows($result) > 0 ? mysqli_query($con, $sql) : null;

// $request = mysqli_fetch_assoc($result);
//     echo $request["id"]. " " . $request["nombre"]. " " . $request["usuario"];

?>
