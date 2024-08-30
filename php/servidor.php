<?php
// -------------------- CONFIGS ----------------------------

// Configuraciones del proyeto

define("CHARSET", "UTF-8");
header("Content-Type: text/html; charset=UTF-8;");
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("UTC");
date_default_timezone_set("America/Guatemala");
session_start();

$fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");

// ------------------- CONEXION SQL-------------------------

// mysqli_connect() Crea una conexión hacia la base de datos, donde espera el host, username y contrasena
$con = mysqli_connect("localhost", "root", ""); // En este caso no existe contrasena en la db, por lo que se deja vacío

// Validación por si falla la conexión a la DB.
if (!$con) {
    die("Error DB connect " . mysqli_connect_error());
}

// mysqli_select_db() Selecciona la base de datos que se usa en el sistema
mysqli_select_db($con, "usuarios2024");
$con->set_charset("utf8");

// -------------------------- PETICIONES HTTP -------------------- //

// -------------------------- PETICION GET ---------------------

// -------------------------- [GET] LISTA DE USUARIOS ALL ------------------- //

// Se inicializa un nuevo Metodo HTTP [GET] donde con "==", se hace referencia al Metodo estricto GET

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
    // ----------------------- [GET] LISTA USUARIOS FILTRO ----------------------- //
    if ($quest == "lista_usuarios_filtro") { // Inicia el parámetro fijo           
    
        /// Se declaran variables, las cuales son extraídas de la entidad Usuarios.
        $usuario = isset($_GET["usuario"]) ? $_GET["usuario"] : "";
        $contrasena = isset($_GET["contrasena"]) ? $_GET["contrasena"] : "";

        // Consulta a MySQL para buscar los usuarios que coincidan con la búsqueda
        $mysql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
        
        // Se ejecuta la consulta y la conexión a la Base De Datos
        $result = mysqli_query($con, $mysql);

        // Se condiciona a la variable $result, la cual contiene la ejecución de la consulta.
        if ($result && mysqli_num_rows($result) > 0) {
            $data = []; // Se inicializa para que traiga los datos como un arrego. Array().

            // Ciclo While que condiciona a $result y lo coloca en una fila, además de asociarla a dicha fila con mysqli_fetch_assoc()
            while ($row = mysqli_fetch_assoc($result)) {
                $db_password = $row["contrasena"];

                // Se utiliza password_verify() como manera de comparar la contrasena ingresada por el usuario con la contrasena
                // encriptada en phpMyAdmin.
                if (password_verify($contrasena, $db_password)) { // Se pasan dos párametros, primero la contrasena que ingresa el usuario y segundo la encriptada en la DB.
                    $data = $row; // Si la contrasena es correcta, devuelve la información y la asigna a una fila del Array().
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

// Se usa $_SERVER para referenciar la variable super global
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // json_decode() decodifica los datos de JSON recibidos
    $data = json_decode(file_get_contents("php://input"), true); // file_get_contents() espera recibir parámetros y funciona como un req.body.

    // Verifica si el quest es para ingresar un usuario
    if ($data["quest"] == "ingresar_usuario") {
        // Asignación de variables
        $nombre = isset($data["nombre"]) ? $con->real_escape_string($data["nombre"]) : ""; // real_escape_string() Evita los caracteres especiales. Evita ISQL
        $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
        $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";

            // Iniciar y guardar en $initial_username el nombre de usuario de la DB.
            $initial_username = $usuario;
            // $new_username guarda el nombre de usuario original de la Base de datos
            $new_username = $initial_username;

            // Variable para incrementar un número y así ser una opción para el usuario si el nombre está ocupado.
            // Se inicializa en 0, para incrementar el numero si el usuario está ocupado.

            $i = 0;

            do {
                // Verifica si el usuario ya existe en BD
                $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$new_username'"; // Consulta para seleccionar el usuario
                $rs = mysqli_query($con, $user_check);
                $result_check = mysqli_num_rows($rs); // mysqli() Obtiene el número de filas de la información extraída de $rs (ejecución de la consulta)
                
                if ($result_check > 0) {
                    // Si el usuario ya existe, se incrementa el contador y genera un nuevo nombre de usuario Ejem: jyaxon1
                    $i++;
                    $new_username = $initial_username . $i; // $new_username obtiene y agrega el nuevo usuario generado
                } else {
                    // Si el usuario no existía en db sale del bucle.`
                    break;
                }
                // Ciclo Do-While para condicionar el incremento del contador. 

                // Repite el proceso hasta encontrar un nombre de usuario adecuado. 
            } while (true); 

            // Ciclo if donde alerta al usuario de que el nombre de usuario ingresado ya está en uso en la DB. Sugiere también un nombre de usuario libre.
            if ($new_username !== $initial_username) {
                $response = [
                    "success" => false, 
                    "alert" => "¡Ya existe un usuario con este nombre!", // Mensaje de advertencia sobre el nombre de usuario repetido.
                    "message" => "Intenta con este usuario generado: " . $new_username // Mensaje para que el usuario escoja otro posible nombre de usuario.
                ];
            } else {
                // Encriptar contraseña en DB
                $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

                // Insertar datos a la entidad Usuarios.
                $mysql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$new_username', '$hashed_password')";

                // Condicionar la ejecución de la consulta $mysql
                if (mysqli_query($con, $mysql)) {
                    // Obtener el ID que generó el INSERT INTO
                    $id_generado = mysqli_insert_id($con);

                    // Crear nuevo arreglo con los datos insertados
                    $response = [
                        "success" => true,
                        "id" => $id_generado,
                        "message" => "Usuario creado exitosamente",
                        "nombre" => $nombre,
                        "usuario" => $new_username,
                        "contrasena" => $hashed_password,
                    ];
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Error al crear el usuario: " . mysqli_error($con) // Mensaje de alerta para el usuario.
                    ];
                }
            }
            
            // Respuesta 200 si todo lo anterior sale correctamente. 
            header("HTTP/1.1 200 OK");
            // Devolver la respuesta en JSON
            echo json_encode($response);
    }
}       

// ------------------------------- [PUT] EDITAR UN USUARIO ------------------------------------ //

// Se inicializa método HTTP PUT para editar / actualizar campos en la entidad Usuarios.

if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data["quest"] == "editar_usuario") {
        // Se definen las variables con escape de caracteres especiales
        $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : null;
        $contrasena = isset($data['contrasena']) ? $con->real_escape_string($data['contrasena']) : null;
        // Variables que permiten editar los campos de la base de datos
        $nombre_nuevo = isset($data["nombre_nuevo"]) ? $con->real_escape_string($data["nombre_nuevo"]) : "";
        $usuario_nuevo = isset($data["usuario_nuevo"]) ? $con->real_escape_string($data["usuario_nuevo"]) : null;
        $contrasena_antigua = isset($data["contrasena_antigua"]) ? $con->real_escape_string($data["contrasena_antigua"]) : "";
        $contrasena_nueva = isset($data["contrasena_nueva"]) ? $con->real_escape_string($data["contrasena_nueva"]) : null;
        $repetir_contrasena_nueva = isset($data["repetir_contrasena_nueva"]) ? $con->real_escape_string($data["repetir_contrasena_nueva"]) : null;
        
        $initial_username = $usuario;
        $nuevo_usuario = $initial_username;
        $increment = 0;

        do {
            // Verifica si el usuario ya existe en BD
            $user_check = "SELECT usuario FROM usuarios WHERE usuario = '$nuevo_usuario'"; // Consulta para seleccionar el usuario
            $rs = mysqli_query($con, $user_check);
            $result_check = mysqli_num_rows($rs); // mysqli() Obtiene el número de filas de la información extraída de $rs (ejecución de la consulta)
            
            if ($result_check > 0) {
                // Si el usuario ya existe, se incrementa el contador y genera un nuevo nombre de usuario Ejem: jyaxon1
                $increment++;
                $nuevo_usuario = $initial_username . $increment; // $new_username obtiene y agrega el nuevo usuario generado
            } else {
                // Si el usuario no existía en db sale del bucle.`
                break;
            }
            // Ciclo Do-While para condicionar el incremento del contador. 

            // Repite el proceso hasta encontrar un nombre de usuario adecuado. 
        } while (true); 

        if (!empty($usuario) && !empty($contrasena)) {
            $sql_verify = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
            $rs_verify = $con->query($sql_verify);

            if ($rs_verify && $rs_verify->num_rows > 0) {
                $user_verify = $rs_verify->fetch_assoc();
                $hashed_password_verify = $user_verify['contrasena'];
                
                // Verifica la contraseña ingresada
                if (password_verify($contrasena, $hashed_password_verify)) {
                    // Verificación de nuevo usuario y contraseña
                    if (!empty($usuario) && !empty($contrasena_antigua)) {
                        $sql_check_user = "SELECT id, contrasena FROM usuarios WHERE usuario = '$usuario'";
                        $rs_check_user = $con->query($sql_check_user);

                        if ($rs_check_user && $rs_check_user->num_rows > 0) {
                            $user = $rs_check_user->fetch_assoc();
                            $hashed_password_current = $user['contrasena'];

                            if (password_verify($contrasena_antigua, $hashed_password_current)) {
                                if ($contrasena_nueva !== null && $contrasena_nueva === $repetir_contrasena_nueva) {
                                    $hashed_password = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
                                } else if ($contrasena_nueva !== $repetir_contrasena_nueva) {
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

                                if ($usuario_nuevo !== $usuario) {
                                    $check_sql = "SELECT id FROM usuarios WHERE usuario = '$usuario_nuevo' AND id != " . $user['id'];
                                    $check_rs = $con->query($check_sql);

                                    if ($check_rs && $check_rs->num_rows > 0) {
                                        $response = [
                                            "success" => false,
                                            "message" => "Usuario ya en uso. Intenta con este usuario: " . $nuevo_usuario
                                        ];
                                        header('Content-Type: application/json');
                                        echo json_encode($response);
                                        exit();
                                    }
                                }

                                $update_fields = [];
                                if ($nombre_nuevo !== "") $update_fields[] = "nombre = '$nombre_nuevo'";
                                if ($usuario_nuevo !== "") $update_fields[] = "usuario = '$usuario_nuevo'";        
                                if ($hashed_password !== $hashed_password_current) $update_fields[] = "contrasena = '$hashed_password'";

                                if (!empty($update_fields)) {
                                    $update_sql = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE usuario = '$usuario'";
                                    $response_sql = $con->query($update_sql);

                                    if(empty($update_fields)){
                                        $response = [
                                            "success" => false,
                                            $nombre_nuevo = $_POST['nombre'],
                                            $usuario_nuevo = $_POST['usuario'],
                                            $contrasena_nueva = $_POST['contraena'],
                                            $repetir_contrasena_nueva = $_POST['contrasena']
                                        ];
                                    }

                                    if ($response_sql) {
                                        $response = [
                                            "success" => true,
                                            "message" => "Datos actualizados",
                                            "usuario" => $usuario_nuevo,
                                            "nombre_cliente" => $nombre_nuevo
                                        ];
                                    } else {
                                        $response = [
                                            "success" => false,
                                            "message" => 'Hubo un error en actualizar los datos'
                                        ];
                                    }
                                } else {
                                    $response = [
                                        "success" => false, 
                                        "message" => "Sin cambios en los datos"
                                    ];
                                }
                            } else {
                                $response = [
                                    "success" => false,
                                    "message" => "La contraseña antigua no es correcta"
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
                            "message" => "El nuevo usuario y contraseña antigua son requeridos"
                        ];
                    }
                } else {
                    $response = [
                        "success" => false,
                        "message" => "Credenciales Inválidas1x"
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
                "message" => "Credenciales Inválidas"
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(["error" => 'Internal Server Error']);
    }
}


// ------------------------- [DELETE] ELIMINAR UN USUARIO ---------------------- //

// Se inicia un nuevo método HTTP DELETE 

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Obtiene y decodifica el JSON de la entrada
    $data = json_decode(file_get_contents("php://input"), true);

    // Verifica si el parámetro "quest" es correcto
    if (isset($data["quest"]) && $data["quest"] == "eliminar_usuario") {
        // Obtiene y limpia los datos de entrada
        $usuario = isset($data['usuario']) ? $con->real_escape_string($data['usuario']) : '';
        $contrasena = isset($data['contrasena']) ? $data['contrasena'] : '';  // No se limpia con real_escape_string
        $repetir_contrasena = isset($data['repetir_contrasena']) ? $data['repetir_contrasena'] : '';  // No se limpia con real_escape_string

        // Verifica que todos los campos necesarios estén presentes
        if (empty($usuario) || empty($contrasena) || empty($repetir_contrasena)) {
            $response = [
                "success" => false,
                "message" => "Usuario, contraseña y repetir contraseña son obligatorios"
            ];
        } elseif ($contrasena != $repetir_contrasena) { 
            // Verifica que las contraseñas coincidan
            $response = [
                "success" => false,
                "message" => "Las contraseñas no coinciden"
            ];
        } else {
            // Verifica si el usuario existe y la contraseña es correcta
            $sql_check_user = "SELECT contrasena FROM usuarios WHERE usuario = '$usuario'";
            $result_check = mysqli_query($con, $sql_check_user);

            if (mysqli_num_rows($result_check) > 0) {
                $row = mysqli_fetch_assoc($result_check);
                $hash = $row['contrasena'];

                // Verifica la contraseña proporcionada con el hash almacenado
                if (password_verify($contrasena, $hash)) {
                    // La contraseña es correcta, procede a eliminar
                    $sql_delete = "DELETE FROM usuarios WHERE usuario = '$usuario'";
                    $result_delete = mysqli_query($con, $sql_delete);

                    if ($result_delete) {
                        $response = [
                            "success" => true,
                            "message" => "Usuario eliminado"
                        ];
                    } else {
                        $response = [
                            "success" => false,
                            "message" => "Error al eliminar el usuario: " . mysqli_error($con)
                        ];
                    }
                } else {
                    // Contraseña incorrecta
                    $response = [
                        "success" => false,
                        "message" => "Usuario o contraseña incorrectos"
                    ];
                }
            } else {
                // Usuario no encontrado
                $response = [
                    "success" => false,
                    "message" => "Usuario no encontrado"
                ];
            }
        }
    } 
    // Configura el tipo de contenido y devuelve la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>



