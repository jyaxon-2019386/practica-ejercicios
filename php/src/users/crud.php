<?php

// Importaciones de archivos
require ('../../middlewares/validator.php');
require ('../../configs/server/servidor.php');


// -------------------- PETICIONES HTTP -------------------- //

// $request_method para manejar el tipo de petición HTTP
$request_method = $_SERVER["REQUEST_METHOD"];

// $quest para manejar la acción específica
$quest = isset($_GET["quest"]) ? $_GET["quest"] : null;


$quest = null; // Iniciar la variable con null para modificarla en el bloque IF

if($request_method == 'GET'){ // Si la petición es un GET 
    $quest = isset($_GET['quest']) ? $_GET['quest'] : null; // En este caso quest recibe que debe ser ingreasado en los Query Parameters.
}else { // De otro modo se ejecuta el codigo de abajo
    $data = json_decode(file_get_contents('php://input'), true); 
    $quest = isset($data['quest']) ? $data['quest'] : null; // Si la peticion es POST, PUT o DELETE la variable quest se debe ingresar en formato JSON
}

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
                    echo json_encode(['error' => 'No se ingresó el usuario y/o contrasena']);
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

                    if (!$nombre || !$usuario || !$contrasena) {
                        header('HTTP/1.1 404 Not Found');
                        echo json_encode(['error' => 'No se ingresaron uno o varios de estos campos: nombre, usuario o contrasena']);
                        break;
                    }
                            
                    if(!validar_nombre($nombre)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'alert' => 'El nombre solo debe contener letras y espacios'
                        ]);
                        break;
                    }

                    if(!validar_usuario($usuario)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'alert' => 'El nombre de usuario solo puede tener letras, número o guiones bajos.'
                        ]);
                        break;
                    }

                    if(!validar_contrasena($contrasena)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'alert' => 'La contrasena debe ser de 8 y 64 caracteres, debe contener una mayuscula, un numero y un caracter especial'
                        ]);
                        break;
                    }
        
                    // Extrae el usuario original de DB eliminando el numero del final y asi validar que no se repita un usuario.
                
                                                // +$ Asegura que los valores esten al final de la cadena
                    $usuario_base = preg_replace('/\d+$/', '', $usuario); // \d+ busca uno o mas valores
                    $usuario_nuevo = $usuario;

                    for ($i = 1; ; $i++) { // Ciclo FOR para crear el numero distintivo del final de usuario nuevo
                        $user_check = "SELECT COUNT(*) as count FROM usuarios WHERE usuario = '$usuario_nuevo'"; // Consulta para verificar si el nombre de usuario ya existe en la base de datos

                        $rs = mysqli_query($con, $user_check);
                        $row = mysqli_fetch_assoc($rs); 

                        if ($row['count'] == 0) { // Si la cuenta es igual a 0, entonces se genera un nuevo usuario que NO EXISTIA en DB.
                            break; // Usuario disponible encontrado
                        }

                        $usuario_nuevo = $usuario_base . $i; // Devuelve nombre_nuevo con el numero del final
                    }
        
                    if ($usuario_nuevo !== $usuario) {
                        header('HTTP/1.1 409 Conflict');
                        $response = [
                            "success" => false,
                            "alert" => "¡Ya existe un usuario con este nombre!",
                            "message" => "Intenta con este usuario generado: " . $usuario_nuevo
                        ];
                    } else {
                        // Proceder con la inserción del nuevo usuario
                        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$usuario_nuevo', '$hashed_password')";
        
                        if (mysqli_query($con, $sql)) {
                            $id_generado = mysqli_insert_id($con);
                            $response = [
                                "success" => true,
                                "id" => $id_generado,
                                "message" => "Usuario creado exitosamente",
                                "nombre" => $nombre,
                                "usuario" => $usuario_nuevo
                            ];
                        } else {
                            $response = [
                                "success" => false,
                                "message" => "Error al crear el usuario: " . mysqli_error($con)
                            ];
                        }
                    }
                    echo json_encode($response);
                    break;
        
                default:
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['error' => 'Quest no encontrado']);
                    break;
            }
            break;

        case 'PUT':
            switch($quest){
                case 'editar_usuario':
                    $data = json_decode(file_get_contents("php://input"), true);
        
                    // Campos obligatorios si el usuario quiere editar su informacion
                    $usuario = $con->real_escape_string($data['usuario'] ?? null);
                    $contrasena = $con->real_escape_string($data['contrasena'] ?? null);

                    // Campos nuevos despues de pasar la validacion
                    $nombre_nuevo = $con->real_escape_string($data['nombre_nuevo'] ?? null); 
                    $usuario_nuevo = $con->real_escape_string($data['usuario_nuevo'] ?? null);
                    $contrasena_nueva = $con->real_escape_string($data['contrasena_nueva'] ?? null);
                    $repetir_contrasena_nueva = $con->real_escape_string($data['repetir_contrasena_nueva'] ?? null);

                    // Si no son ingresados el usuario o contrasena
                    if(!$usuario || !$contrasena){
                        header('HTTP/1.1 404 Not Found');
                        echo json_encode([
                            'error' => 'No se ingresó el usuario y/o contrasena'
                        ]);
                        break;
                    }

                    // Validaciones de caracteres
                    if(!validar_nombre($nombre_nuevo)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'error' => 'El nombre solo debe contener letras y espacios'
                        ]);
                        break;
                    }

                    if(!validar_usuario($usuario_nuevo)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'error' => 'El usuario solo puede contener letras, numeros o guiones bajos'
                        ]);
                    }

                    if(!validar_contrasena($contrasena_nueva)){
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode([
                            'error' => 'La contrasena debe ser de 8 y 64 caracteres, debe contener una mayuscula, un numero y un caracter especial'
                        ]);
                        break;
                    }
                    
                    // Condiciona el usuario y contrasena para despues ejecutar la consulta UPDATE
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
                                if ($contrasena && password_verify($contrasena, $hashed_password_current)) {
                                    if ($contrasena_nueva && $contrasena_nueva === $repetir_contrasena_nueva) {
                                        $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                                    } else if ($contrasena_nueva !== $repetir_contrasena_nueva) {
                                        echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden"]);
                                        exit();
                                    }
                                } else {
                                    $hashed_password = $hashed_password_current;
                                }
                
                                // Validar y generar nuevo usuario si se cambia
                                if ($usuario_nuevo && $usuario_nuevo !== $usuario) {
                                    $usuario_generado = $usuario_nuevo;

                                    for($i = 1; ;$i++){
                                        $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$usuario_generado'";
                                        $rs_user_check = mysqli_query($con, $user_check);
    
                                        if(mysqli_num_rows($rs_user_check) > 0){
                                            $usuario_generado = $usuario_nuevo . $i;
                                        }else {
                                            break;
                                        }
                                    }
                
                                    if ($usuario_generado !== $usuario_nuevo) {
                                        header('HTTP/1.1 409 Conflict');
                                        echo json_encode(["success" => false, "message" => "Este usuario ya existe. Intenta con este nombre nuevo: $usuario_generado"], JSON_PRETTY_PRINT);
                                        exit();
                                    }
                                    
                                    $usuario_nuevo = $usuario_generado;
                                }
                
                                // Array que almacena la informacion actualizada o no
                                $fields = [];

                                if(!empty($data['nombre_nuevo'])){
                                    $fields[] = "nombre = '{$con->real_escape_string($data['nombre_nuevo'])}'";
                                }

                                if(!empty($data['usuario_nuevo'])){
                                    $fields[] = "usuario = '{$con->real_escape_string($data['usuario_nuevo'])}'";
                                }
                                
                                // if ($hashed_password !== $hashed_password_current) $fields[] = "contrasena = '$hashed_password'";

                                if(!empty($data['contrasena_nueva']) && $data['contrasena_nueva'] === $data['repetir_contrasena_nueva']){
                                    $fields[] = "contrasena = '" . password_hash($data['contrasena_nueva'], PASSWORD_DEFAULT) . "'";
                                }

                                if (!empty($fields)) { // Si los campos que no son obligatorios estan vacios, entonces se conserva la informacion antes de la consulta UPDATE.
                                    $update_sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = $user_id";
                                    $response_sql = mysqli_query($con, $update_sql);
                
                                    $response = $response_sql
                                        ? ["success" => true, "message" => "Datos actualizados", "usuario" => $usuario_nuevo, "nombre" => $nombre_nuevo]
                                        : ["success" => false, "message" => "Hubo un error al actualizar sus datos."];
                                } else {
                                    header('HTTP/1.1 304 Not Modified');
                                    $response = [
                                        "success" => false, 
                                        "message" => "No se realizaron cambios."
                                    ];
                                }
                            } else {
                                header('HTTP/1.1 400 Bad Request');
                                $response = [
                                    "success" => false, 
                                    "message" => "La contrasena es incorrecta"
                                ];
                            }
                        } else {
                            header('HTTP/1.1 404 Not Found');
                            $response = [
                                "success" => false, 
                                "message" => "Usuario no encontrado"
                            ];
                        }
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        $response = [
                            "success" => false, 
                            "message" => "Error al editar el usuario"
                        ];
                    }
                    echo json_encode($response);
                    break;
                default:
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode([
                        'error' => 'Quest no encontrado'
                    ]);
            }
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
                    header('HTTP/1.1 400 Bad Request');
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
                            header('HTTP/1.1 400 Bad Request');
                            $response = [
                                "success" => false,
                                "message" => "Error al eliminar el usuario"
                            ];
                        }
                    } else {
                        header('HTTP/1.1 400 Bad Request');
                        $response = [
                            "success" => false,
                            "message" => "Contrasena incorrecta"
                        ];
                    }
                } else {
                    header('HTTP/1.1 400 Bad Request');
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