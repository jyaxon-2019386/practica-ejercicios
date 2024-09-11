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
    $con = mysqli_connect("localhost", "root", "");
    if (!$con) {
        die("Error DB connect " . mysqli_connect_error());
    }
    mysqli_select_db($con, "usuarios2024");
    $con->set_charset("utf8");

// -------------------------- PETICIONES HTTP -------------------- //

// $request_method funciona como un condicionante para saber que tipo de petición HTTP
$request_method = $_SERVER["REQUEST_METHOD"];

// $quest condiciona los casos en el switch case
$quest = isset($_GET["quest"]) ? $_GET["quest"] : null;

// Inicio del switch case
switch ($request_method) {
    case 'GET':
        switch ($quest) {
            case 'lista_usuarios':
                $mysql = "SELECT * FROM usuarios;";
                $result = mysqli_query($con, $mysql);
                if ($result) {
                    $data = [];
                    while ($row = mysqli_fetch_assoc($result)) { // 
                        $data[] = $row;
                    }
                    echo json_encode($data);
                } else {
                    header("HTTP/1.1 404 Not Found");
                    echo json_encode(["error" => "No se encontraron datos"]);
                }
                break;

            case 'lista_usuarios_filtro':
              $letra = isset($_GET['letra']) ? $_GET['letra'] : '';
              $sql = "SELECT * FROM usuarios WHERE id LIKE '%$letra%' OR nombre LIKE '%$letra%' OR usuario LIKE '%$letra%'";

              $rs = mysqli_query($con, $sql);

              if($rs){
                $data = array();
                while($row = mysqli_fetch_assoc($rs)){
                    $data[] = $row;
                }

                echo json_encode($data);
              }else {
                header('HTTP/1.1 404 Not Found');
                echo json_encode(array("error" => "No se encontraron datos"));
              }
        }

        case 'login':
            $usuario = isset($_GET['usuario']) ? $con->real_escape_string($_GET['usuario']) : '';   
            $contrasena = isset($_GET['contrasena']) ? $con->real_escape_string($_GET['contrasena']) : '';

            if (!empty($usuario) && !empty($contrasena)) {
                $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
                $result = mysqli_query($con, $sql);

                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    $hashed_password = $user['contrasena'];

                    if (password_verify($contrasena, $hashed_password)) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Login exitoso",
                            "user" => $user
                        ]);
                    } else {
                        header('HTTP/1.1 401 Unauthorized');
                        echo json_encode(["error" => "Credenciales inválidas"]);
                    }
                } else {
                    header('HTTP/1.1 404 Not Found');
                    echo json_encode(["error" => "Usuario no encontrado"]);
                }
            } 
        break;
    
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $nombre = isset($data['nombre']) ? $con->real_escape_string($data['nombre']) : '';
        $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : '';
        $contrasena = isset($data['contrasena']) ? $con->real_escape_string($data['contrasena']) : '';

        $initial_username = $usuario;
        $new_username = $initial_username;
        $i = 0;
        
        do {
            $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$new_username'";
            $rs = mysqli_query($con, $user_check);
            if (mysqli_num_rows($rs) > 0) {
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
                    "usuario" => $usuario,
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
    
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        // Se definen las variables con escape de caracteres especiales
        $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : null;
        $contrasena = isset($data['contrasena']) ? $con->real_escape_string($data['contrasena']) : null;
        $nombre_nuevo = isset($data["nombre_nuevo"]) ? $con->real_escape_string($data["nombre_nuevo"]) : "";
        $usuario_nuevo = isset($data["usuario_nuevo"]) ? $con->real_escape_string($data["usuario_nuevo"]) : null;
        $contrasena_antigua = isset($data["contrasena_antigua"]) ? $con->real_escape_string($data["contrasena_antigua"]) : "";
        $contrasena_nueva = isset($data["contrasena_nueva"]) ? $con->real_escape_string($data["contrasena_nueva"]) : "";
        $repetir_contrasena_nueva = isset($data["repetir_contrasena_nueva"]) ? $con->real_escape_string($data["repetir_contrasena_nueva"]) : "";

        if (!empty($usuario) && !empty($contrasena)) {
            $sql_verify = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
            $rs_verify = mysqli_query($con, $sql_verify);

            if ($rs_verify && $rs_verify->num_rows > 0) {
                $user_verify = $rs_verify->fetch_assoc();
                $hashed_password_verify = $user_verify['contrasena'];

                // Verifica la contraseña ingresada
                if (password_verify($contrasena, $hashed_password_verify)) {
                    $user_id = $user_verify['id'];

                    $hashed_password_current = $hashed_password_verify;

                    if(!empty($contrasena_antigua)){
                        if(password_verify($contrasena_antigua, $hashed_password_current)){
                            if(!empty($contrasena_nueva) && $contrasena_nueva === $repetir_contrasena_nueva){
                                $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                            }else if(!empty($contrasena_nueva) && $contrasena_nueva !== $repetir_contrasena_nueva){
                                $response = [
                                    "success" => false,
                                    "message" => "Las contrasenas no coinciden"
                                ];
                                echo json_encode($response);
                                exit();
                            }
                            $hashed_password = $hashed_password_current;
                        }

                    }else {
                        $response = [
                            "success" => false,
                            "message" => "La contrasena antigua no es correcta"
                        ];
                        echo json_encode($response);    
                        exit();
                    }

                    if (!empty($usuario)) {
                        $sql_check_usuario = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
                        $rs_check_usuario = $con->query($sql_check_usuario);

                        if ($rs_check_usuario && $rs_check_usuario->num_rows > 0) {
                            $user = $rs_check_usuario->fetch_assoc();
                            $hashed_password_current = $user['contrasena'];

                            if (!empty($contrasena_antigua)) {
                                if (password_verify($contrasena_antigua, $hashed_password_current)) {
                                    if (!empty($contrasena_nueva) && $contrasena_nueva === $repetir_contrasena_nueva) {
                                        $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                                    } else if (!empty($contrasena_nueva) && $contrasena_nueva !== $repetir_contrasena_nueva) {
                                        $response = [
                                            "success" => false,
                                            "message" => "Las contraseñas no coinciden"
                                        ];
                                        header('Content-Type: application/json');
                                        echo json_encode($response);
                                        exit();
                                    } else {
                                        $hashed_password = $hashed_password_current;
                                    }
                                } else {
                                    $response = [
                                        "success" => false,
                                        "message" => "La contraseña antigua no es correcta"
                                    ];
                                    header('Content-Type: application/json');
                                    echo json_encode($response);
                                    exit();
                                }
                            } else {
                                $hashed_password = $hashed_password_current;
                            }
                    
                                if($usuario_nuevo !== $usuario){
                                    $usuario_generado = $usuario_nuevo; // Redundancia
                                    $incremento = 0;
    
                                    do{
                                        $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$usuario_generado'";
                                        $rs_user_check = mysqli_query($con, $user_check);
    
                                        if(mysqli_num_rows($rs_user_check) > 0){
                                            $incremento++;
                                            $usuario_generado = $usuario_nuevo . $incremento;
                                        }else{
                                            break;
                                        }
                                    }while(true);
                                }
                            
                            

                            if($usuario_generado !== $usuario_nuevo){
                                $response = [
                                    "success" => false,
                                    "message" => "Este usuario ya existe. Intenta con este nuevo usuario generado: " . $usuario_generado 
                                ];
                                echo json_encode($response);
            
                            }

                            $usuario = $usuario_generado;
                        } 

                        $update_fields = [];

                        if(!empty($nombre_nuevo)) $update_fields[] = "nombre = '$nombre_nuevo'";
                        if(!empty($usuario_nuevo)) $update_fields[] = "usuario = '$usuario_nuevo'";
                        if($hashed_password !== $hashed_password_current) $update_fields[] = "contrasena = '$hashed_password'";

                        if(!empty($update_fields)){
                            $update_sql = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE id = $user_id";
                            error_log($update_sql); 
                            $response_sql = mysqli_query($con, $update_sql);
                            

                            if($response_sql){
                                $response = [
                                    "success" => true,
                                    "message" => "Datos actualizados",
                                    "usuario" => $usuario,
                                    "nombre" => $nombre_nuevo
                                ];
                            }else {
                                $response = [
                                    "success" => false,
                                    "message" => "Hubo un error al actualizar sus datos."
                                ];
                            }
                        }else {
                            $respone = [
                                "success" => false,
                                "message" => "No hubieron cambios"
                            ];
                        }
                    } else {
                        $response = [
                            "success" => false,
                            "message" => "Credenciales inválidas"
                        ];
                    }
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
                "message" => "Error al editar el usuario"
            ];
        }

        echo json_encode($response);
        break;


    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
    
        // Validar que los campos necesarios existan
        if (isset($data["usuario"]) && isset($data["contrasena"]) && isset($data["repetir_contrasena"])) {
            $usuario = $con->real_escape_string($data["usuario"]);
            $contrasena = $con->real_escape_string($data["contrasena"]);
            $repetir_contrasena = $con->real_escape_string($data["repetir_contrasena"]);
            
            // Validar que las contraseñas coincidan
            if ($contrasena !== $repetir_contrasena) {
                $response = [
                    "success" => false,
                    "message" => "Las contraseñas no coinciden"
                ];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Verificar que el usuario exista en la base de datos y que la contraseña sea correcta
            $sql = "SELECT contrasena FROM usuarios WHERE usuario = '$usuario'";
            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $hashed_password = $user['contrasena'];

                // Verificar la contraseña
                if (password_verify($contrasena, $hashed_password)) {
                    // Consulta eliminar usuario
                    $sql = "DELETE FROM usuarios WHERE usuario = '$usuario'";
                    $rs = mysqli_query($con, $sql);

                    if ($rs) {
                        $response = [
                            "success" => true,
                            "message" => "Usuario eliminado exitosamente"
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
                        "message" => "La contraseña es incorrecta"
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
                "message" => "Se requiere el usuario, la contraseña y la repetición de la contraseña"
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        break;
}
?>
