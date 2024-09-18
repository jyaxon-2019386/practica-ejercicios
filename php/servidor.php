<?php
// -------------------- CONFIGS ----------------------------

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

// -------------------- VARIABLES PARA INTENTOS DE LOGIN --------------------
$limite_intentos = 5; // Número máximo de intentos permitidos
$tiempo_bloqueo = 300; // 5 minutos de bloqueo

// -------------------- FUNCIONES AUXILIARES --------------------

// Validar el formato del nombre y usuario
function validar_nombre_usuario($input) {
    // Solo permitir caracteres alfanuméricos, guion bajo y punto
    return preg_match("/^[a-zA-Z0-9._]{3,20}$/", $input);
}

// Validar si el nombre contiene solo caracteres alfabéticos
function validar_nombre($input) {
    // Solo permitir caracteres alfabéticos y espacios
    return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/", $input);
}

// Validar contraseña (mínimo 8 caracteres, al menos una mayúscula, un número y un símbolo especial)
function validar_contrasena($input) {
    return preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,64}$/", $input);
}

// Limitar la longitud máxima de los campos
function limitar_longitud($input, $max_length) {
    return strlen($input) <= $max_length;
}

// Incrementar intentos fallidos de inicio de sesión
function incrementar_intentos($usuario) {
    global $con, $limite_intentos, $tiempo_bloqueo;
    
    $sql = "SELECT intentos_fallidos, ultimo_intento FROM usuarios WHERE usuario = '$usuario'";
    $result = mysqli_query($con, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $intentos = $row['intentos_fallidos'];
        $ultimo_intento = strtotime($row['ultimo_intento']);
        $ahora = time();
        
        if ($ahora - $ultimo_intento > $tiempo_bloqueo) {
            $intentos = 0; // Reinicia el contador si ha pasado el tiempo de bloqueo
        }

        $intentos++;

        if ($intentos >= $limite_intentos) {
            echo json_encode([
                "success" => false,
                "message" => "Cuenta bloqueada temporalmente por demasiados intentos fallidos. Intenta nuevamente en unos minutos."
            ]);
            exit();
        }

        // Actualizar el contador y el tiempo del último intento
        $sql_update = "UPDATE usuarios SET intentos_fallidos = $intentos, ultimo_intento = NOW() WHERE usuario = '$usuario'";
        mysqli_query($con, $sql_update);
    } else {
        // Si el usuario no existe, no hacemos nada
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado."
        ]);
        exit();
    }
}

// Reiniciar los intentos fallidos tras un inicio de sesión exitoso
function reiniciar_intentos($usuario) {
    global $con;
    $sql = "UPDATE usuarios SET intentos_fallidos = 0 WHERE usuario = '$usuario'";
    mysqli_query($con, $sql);
}

// -------------------- PETICIONES HTTP -------------------- //

$request_method = $_SERVER["REQUEST_METHOD"];
$quest = isset($_GET["quest"]) ? $_GET["quest"] : null;

switch ($request_method) {
    case 'POST':
        switch ($quest) {
            case 'ingresar_usuario':
                $data = json_decode(file_get_contents("php://input"), true);

                $nombre = isset($data['nombre']) ? $con->real_escape_string($data['nombre']) : '';
                $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : '';
                $contrasena = isset($data['contrasena']) ? $con->real_escape_string($data['contrasena']) : '';

                // Validaciones de formato y longitud
                if (!validar_nombre($nombre)) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => 'El nombre solo debe contener letras y espacios.']);
                    break;
                }
                if (!validar_nombre_usuario($usuario)) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => 'El nombre de usuario solo puede contener caracteres alfanuméricos, guion bajo y punto.']);
                    break;
                }
                if (!validar_contrasena($contrasena)) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => 'La contraseña debe tener entre 8 y 64 caracteres, incluir al menos una mayúscula, un número y un carácter especial.']);
                    break;
                }

                // Comprobación y generación de nuevo nombre de usuario si ya existe
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

    case 'POST':
        switch ($quest) {
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
                        reiniciar_intentos($usuario); // Reiniciar los intentos fallidos
                        echo json_encode([
                            "success" => true,
                            "message" => "Login exitoso",
                            "greeting" => 'Bienvenido de nuevo, ' . $usuario,
                            "usuario" => $usuario
                        ]);
                    } else {
                        incrementar_intentos($usuario); // Incrementar intentos fallidos
                        header('HTTP/1.1 401 Unauthorized');
                        echo json_encode(["error" => "Credenciales inválidas"]);
                    }
                } else {
                    incrementar_intentos($usuario); // Incrementar intentos fallidos
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

    default:
        header('HTTP/1.1 405 Method Not Allowed');
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>
