<?php
// -------------------- CONFIGS ----------------------------

// Configuraciones del proyecto
define("CHARSET", "UTF-8");
header("Content-Type: text/html; charset=UTF-8;");
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("UTC");
date_default_timezone_set("America/Guatemala");
session_start();

$fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");

// ------------------- CONEXION SQL-------------------------

// mysqli_connect() Crea una conexión hacia la base de datos, donde espera el host, username y contraseña
$con = mysqli_connect("localhost", "root", ""); // En este caso no existe contraseña en la db, por lo que se deja vacío

// Validación por si falla la conexión a la DB.
if (!$con) {
    die("Error DB connect " . mysqli_connect_error());
}

// mysqli_select_db() Selecciona la base de datos que se usa en el sistema
mysqli_select_db($con, "usuarios2024");
$con->set_charset("utf8");

// -------------------------- PETICIONES HTTP -------------------- //

$quest = isset($_REQUEST["quest"]) ? $_REQUEST["quest"] : null;

// Determina el método de la petición HTTP
switch ($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        handleGetRequest($quest);
        break;
    case "POST":
        handlePostRequest();
        break;
    case "PUT":
        handlePutRequest();
        break;
    case "DELETE":
        handleDeleteRequest();
        break;
    default:
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["error" => "Método no permitido"]);
        break;
}

// ------------------- FUNCIONES DE MANEJO DE PETICIONES ------------------- //

function handleGetRequest($quest) {
    global $con;

    switch ($quest) {
        case "lista_usuarios":
            $mysql = "SELECT * FROM usuarios;";
            $result = mysqli_query($con, $mysql);
            if ($result) {
                $data = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = $row;
                }
                echo json_encode($data);
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(["error" => "No se encontraron datos"]);
            }
            break;

        case "lista_usuarios_filtro":
            $letra = isset($_GET["letra"]) ? $_GET["letra"] : "";
            $usuario = isset($_GET["usuario"]) ? $_GET["usuario"] : "";
            $contrasena = isset($_GET["contrasena"]) ? $_GET["contrasena"] : "";

            $mysql = "SELECT * FROM usuarios WHERE (id LIKE '%$letra%' OR nombre LIKE '%$letra%' OR usuario LIKE '%$letra%' OR contrasena LIKE '%$letra%') AND usuario = '$usuario'";
            $result = mysqli_query($con, $mysql);

            if ($result && mysqli_num_rows($result) > 0) {
                $data = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $db_password = $row["contrasena"];
                    if (password_verify($contrasena, $db_password)) {
                        $data = $row;
                        break;
                    }
                }
                if (!empty($data)) {
                    echo json_encode($data);
                } else {
                    header("HTTP/1.1 401 Unauthorized");
                    echo json_encode(["error" => "Usuario o contraseña incorrectos"]);
                }
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(["error" => "Usuario no encontrado"]);
            }
            break;

        default:
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Petición GET inválida"]);
            break;
    }
}

function handlePostRequest() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    switch ($data["quest"]) {
        case "ingresar_usuario":
            $nombre = isset($data["nombre"]) ? $con->real_escape_string($data["nombre"]) : "";
            $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
            $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";

            $initial_username = $usuario;
            $new_username = $initial_username;
            $i = 0;

            do {
                $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$new_username'";
                $rs = mysqli_query($con, $user_check);
                $result_check = mysqli_num_rows($rs);
                
                if ($result_check > 0) {
                    $i++;
                    $new_username = $initial_username . $i;
                } else {
                    break;
                }
            } while (true);

            if ($new_username !== $initial_username) {
                $response = [
                    "success" => false,
                    "alert" => "¡Ya existe un usuario con este nombre!",
                    "message" => "Intenta con este usuario generado: " . $new_username
                ];
            } else {
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                $mysql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$new_username', '$hashed_password')";

                if (mysqli_query($con, $mysql)) {
                    $id_generado = mysqli_insert_id($con);
                    $response = [
                        "success" => true,
                        "message" => "Usuario creado exitosamente",
                        "id" => $id_generado,
                        "nombre" => $nombre,
                        "usuario" => $new_username,
                        "contrasena" => $hashed_password,
                    ];
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Error al crear el usuario: " . mysqli_error($con)
                    ];
                }
            }
            
            header("HTTP/1.1 200 OK");
            echo json_encode($response);
            break;

        default:
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Petición POST inválida"]);
            break;
    }
}

function handlePutRequest() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["quest"]) && $data["quest"] == "editar_usuario") {
        $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
        $contrasena_antigua = isset($data["contrasena_antigua"]) ? $con->real_escape_string($data["contrasena_antigua"]) : "";
        $nombre = isset($data["nombre"]) ? $con->real_escape_string($data["nombre"]) : null;
        $contrasena_nueva = isset($data["contrasena_nueva"]) ? $con->real_escape_string($data["contrasena_nueva"]) : null;
        $repetir_contrasena_nueva = isset($data["repetir_contrasena_nueva"]) ? $con->real_escape_string($data["repetir_contrasena_nueva"]) : null;

        if (!empty($usuario) && !empty($contrasena_antigua)) {
            $sql = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $hashed_password_current = $user['contrasena'];

                if (password_verify($contrasena_antigua, $hashed_password_current)) {
                    if ($contrasena_nueva !== null && $contrasena_nueva === $repetir_contrasena_nueva) {
                        $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                    } elseif ($contrasena_nueva !== null) {
                        $response = [
                            "success" => false,
                            "message" => "Las contraseñas nuevas no coinciden."
                        ];
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit;
                    } else {
                        $hashed_password = $hashed_password_current;
                    }

                    if ($usuario && $usuario !== $data["usuario"]) {
                        $check_sql = "SELECT id FROM usuarios WHERE usuario = '$usuario' AND id != " . $user['id'];
                        $check_result = mysqli_query($con, $check_sql);
                        if (mysqli_num_rows($check_result) > 0) {
                            $response = [
                                "success" => false,
                                "message" => "El nombre de usuario ya existe."
                            ];
                            header('Content-Type: application/json');
                            echo json_encode($response);
                            exit;
                        }
                    }

                    $update_fields = [];
                    if ($nombre !== null) $update_fields[] = "nombre = '$nombre'";
                    if ($hashed_password !== $hashed_password_current) $update_fields[] = "contrasena = '$hashed_password'";

                    if (!empty($update_fields)) {
                        $update_sql = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE usuario = '$usuario'";
                        $update_result = mysqli_query($con, $update_sql);

                        if ($update_result) {
                            $response = [
                                "success" => true,
                                "message" => "Datos actualizados exitosamente"
                            ];
                        } else {
                            $response = [
                                "success" => false,
                                "message" => "Error al actualizar los datos"
                            ];
                        }
                    } else {
                        $response = [
                            "success" => false,
                            "message" => "No hay cambios que realizar"
                        ];
                    }
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Contraseña antigua incorrecta"
                    ];
                }
            } else {
                $response = [
                    "success" => false,
                    "message" => "Usuario no encontrado"
                ];
            }
        } else {
            $response = [
                "success" => false,
                "message" => "Datos insuficientes"
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(["error" => "Petición PUT inválida"]);
    }
}

function handleDeleteRequest() {
    global $con;
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["quest"]) && $data["quest"] == "eliminar_usuario") {
        $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
        $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";

        $sql = "SELECT contrasena FROM usuarios WHERE usuario = '$usuario'";
        $result = mysqli_query($con, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $hashed_password = $user["contrasena"];

            if (password_verify($contrasena, $hashed_password)) {
                $delete_sql = "DELETE FROM usuarios WHERE usuario = '$usuario'";
                $delete_result = mysqli_query($con, $delete_sql);

                if ($delete_result) {
                    $response = [
                        "success" => true,
                        "message" => "Usuario eliminado exitosamente"
                    ];
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Error al eliminar el usuario"
                    ];
                }
            } else {
                $response = [
                    "success" => false,
                    "message" => "Contraseña incorrecta"
                ];
            }
        } else {
            $response = [
                "success" => false,
                "message" => "Usuario no encontrado"
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(["error" => "Petición DELETE inválida"]);
    }
}
?>
