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

// ------------------- CONEXION SQL -------------------------
$con = mysqli_connect("localhost", "root", "");
if (!$con) {
    die("Error DB connect " . mysqli_connect_error());
}
mysqli_select_db($con, "usuarios2024");
$con->set_charset("utf8");

// -------------------- PETICIONES HTTP -------------------- //

// $request_method para manejar el tipo de petición HTTP
$request_method = $_SERVER["REQUEST_METHOD"];

// $quest para manejar la acción específica
$quest = isset($_GET["quest"]) ? $_GET["quest"] : null;

switch ($request_method) {
    case 'GET':
        switch ($quest) {
            case 'lista_usuarios':
                $mysql = "SELECT * FROM usuarios";
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

            case 'lista_usuarios_filtro':
                $letra = isset($_GET['letra']) ? $con->real_escape_string($_GET['letra']) : '';

                if (!$letra) {
                    header("HTTP/1.1 404 Not Found");
                    echo json_encode(['error' => 'No se ingresó una letra o palabra.']);
                    break;
                }

                $sql = "SELECT * FROM usuarios WHERE id LIKE '%$letra%' OR nombre LIKE '%$letra%' OR usuario LIKE '%$letra%'";
                $rs = mysqli_query($con, $sql);

                if ($rs) {
                    $data = array();
                    while ($row = mysqli_fetch_assoc($rs)) {
                        $data[] = $row;
                    }

                    echo json_encode($data);
                } else {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(["error" => "No se encontraron datos"]);
                }
                break;

            case 'login':
                $usuario = isset($_GET['usuario']) ? $con->real_escape_string($_GET['usuario']) : '';
                $contrasena = isset($_GET['contrasena']) ? $con->real_escape_string($_GET['contrasena']) : '';

                if (!$usuario || !$contrasena) {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(['error' => 'No se ingresó el usuario y/o contraseña']);
                    break;
                }

                $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
                $result = mysqli_query($con, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    $hashed_password = $user['contrasena'];

                    if (password_verify($contrasena, $hashed_password)) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Login exitoso",
                            "greeting" => 'Bienvenido de nuevo, ' . $usuario,
                            "usuario" => $usuario
                        ]);
                    } else {
                        header('HTTP/1.1 401 Unauthorized');
                        echo json_encode(["error" => "Credenciales inválidas"]);
                    }
                } else {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(["error" => "Usuario no encontrado"]);
                }
                break;

            default:
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(["error" => "Quest no encontrado"]);
                break;
        }
        break;

        case 'POST':
            switch ($quest) {
                case 'ingresar_usuario':
                    $data = json_decode(file_get_contents("php://input"), true);
        
                    $nombre = isset($data['nombre']) ? $con->real_escape_string($data['nombre']) : '';
                    $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : '';
                    $contrasena = isset($data['contrasena']) ? $con->real_escape_string($data['contrasena']) : '';
        
                    // Validar si los campos requeridos están presentes
                    if (!$nombre || !$usuario || !$contrasena) {
                        header('HTTP/1.1 404 Not Found');
                        echo json_encode(['error' => 'No se ingresaron uno o varios de estos campos: nombre, usuario o contrasena']);
                        break;
                    }
        
                    // Comprobación y generación de nuevo nombre de usuario si ya existe
                    // Probar con new_usaurio si hay fallos
                    $usuario_nuevo = $usuario;
                    for ($i = 1; ; $i++) {
                        $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$usuario_nuevo'";
                        $rs = mysqli_query($con, $user_check);
        
                        if (mysqli_num_rows($rs) == 0) {
                            break; // Sale del bucle si no encuentra el usuario
                        }
                        $usuario_nuevo = $usuario . $i; // Genera un nuevo nombre de usuario
                    }
        
                    if ($usuario_nuevo !== $usuario) {
                        $response = [
                            "success" => false,
                            "alert" => "¡Ya existe un usuario con este nombre!",
                            "message" => "Intenta con este usuario generado: " . $usuario_nuevo
                        ];
                    } else {
                        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$usuario_nuevo', '$hashed_password')";
        
                        if (mysqli_query($con, $sql)) {
                            $id_generado = mysqli_insert_id($con);
                            $response = [
                                "success" => true,
                                "message" => "Usuario creado exitosamente",
                                "id" => $id_generado,
                                "nombre" => $nombre,
                                "usuario" => $usuario_nuevo,
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
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => 'Quest no encontrado']);
                    break;
            }
        break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
        
            // Escapamos las variables
            $usuario = $con->real_escape_string($data['usuario'] ?? null);
            $contrasena = $con->real_escape_string($data['contrasena'] ?? null);
            $nombre_nuevo = $con->real_escape_string($data["nombre_nuevo"] ?? "");
            $usuario_nuevo = $con->real_escape_string($data["usuario_nuevo"] ?? null);
            $contrasena_antigua = $con->real_escape_string($data["contrasena_antigua"] ?? "");
            $contrasena_nueva = $con->real_escape_string($data["contrasena_nueva"] ?? "");
            $repetir_contrasena_nueva = $con->real_escape_string($data["repetir_contrasena_nueva"] ?? "");
        
            if ($usuario && $contrasena) {
                $sql_verify = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
                $rs_verify = mysqli_query($con, $sql_verify);
        
                if ($rs_verify && $rs_verify->num_rows > 0) {
                    $user_verify = $rs_verify->fetch_assoc();
                    $user_id = $user_verify['id'];
                    $hashed_password_current = $user_verify['contrasena'];
        
                    // Verificamos la contraseña actual
                    if (password_verify($contrasena, $hashed_password_current)) {
                        // Cambiar la contraseña si se proporciona la antigua y las nuevas coinciden
                        if ($contrasena_antigua && password_verify($contrasena_antigua, $hashed_password_current)) {
                            if ($contrasena_nueva && $contrasena_nueva === $repetir_contrasena_nueva) {
                                $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                            } else if ($contrasena_nueva !== $repetir_contrasena_nueva) {
                                echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden"], JSON_PRETTY_PRINT);
                                exit();
                            }
                        } else {
                            $hashed_password = $hashed_password_current;
                        }
        
                        // Validar y generar nuevo usuario si se cambia
                        if ($usuario_nuevo && $usuario_nuevo !== $usuario) {
                            $usuario_generado = $usuario_nuevo;
        
                            for ($incremento = 0; $incremento < 10; $incremento++) {
                                $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$usuario_generado'";
                                $rs_user_check = mysqli_query($con, $user_check);
        
                                if (mysqli_num_rows($rs_user_check) > 0) {
                                    $usuario_generado = $usuario_nuevo . $incremento;
                                } else {
                                    break;
                                }
                            }
        
                            if ($usuario_generado !== $usuario_nuevo) {
                                echo json_encode(["success" => false, "message" => "Este usuario ya existe. Intenta con este nombre nuevo: $usuario_generado"], JSON_PRETTY_PRINT);
                                exit();
                            }
                            
                            $usuario_nuevo = $usuario_generado;
                        }
        
                        // Actualización de los campos
                        $update_fields = [];
                        if ($nombre_nuevo) $update_fields[] = "nombre = '$nombre_nuevo'";
                        if ($usuario_nuevo) $update_fields[] = "usuario = '$usuario_nuevo'";
                        if ($hashed_password !== $hashed_password_current) $update_fields[] = "contrasena = '$hashed_password'";
        
                        if (!empty($update_fields)) {
                            $update_sql = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE id = $user_id";
                            $response_sql = mysqli_query($con, $update_sql);
        
                            $response = $response_sql
                                ? ["success" => true, "message" => "Datos actualizados", "usuario" => $usuario_nuevo, "nombre" => $nombre_nuevo]
                                : ["success" => false, "message" => "Hubo un error al actualizar sus datos."];
                        } else {
                            $response = ["success" => false, "message" => "No se realizaron cambios."];
                        }
                    } else {
                        $response = ["success" => false, "message" => "La contraseña es incorrecta"];
                    }
                } else {
                    $response = ["success" => false, "message" => "Usuario no encontrado"];
                }
            } else {
                $response = ["success" => false, "message" => "Error al editar el usuario"];
            }
        
            echo json_encode($response);
            break;        

    case 'DELETE':
        switch ($quest) {
            case 'eliminar_usuario':
                $data = json_decode(file_get_contents("php://input"), true);
                $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : '';
                $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : '';
                $repetir_contrasena = isset($data["repetir_contrasena"]) ? $con->real_escape_string($data["repetir_contrasena"]) : '';
                
                if (!$usuario || !$contrasena || !$repetir_contrasena) {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(['error' => 'No se ingresaron uno o varios de estos campos: usuario, contrasena o repetir_contrasena']);
                    break;
                }

                if ($contrasena !== $repetir_contrasena) {
                    $response = [
                        "success" => false,
                        "message" => "Las contraseñas no coinciden"
                    ];
                    echo json_encode($response);
                    break;
                }

                $sql = "SELECT contrasena FROM usuarios WHERE usuario = '$usuario'";
                $result = mysqli_query($con, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    $hashed_password = $user["contrasena"];

                    if (password_verify($contrasena, $hashed_password)) {
                        $sql_delete = "DELETE FROM usuarios WHERE usuario = '$usuario'";
                        if (mysqli_query($con, $sql_delete)) {
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
                echo json_encode($response);
                break;

            default:
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['error' => 'Quest no encontrado']);
                break;
        }
        break;

    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
