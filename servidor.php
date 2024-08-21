<?php
    // -------------------- CONFIGS ----------------------------

    // Configuraciones del proyeto

    define("CHARSET", "UFT-8");
    header("Content-Type: text/html; charset=UTF-8;");
    header("Access-Control-Allow-Origin: *");
    date_default_timezone_set("UTC");
    date_default_timezone_set("America/Guatemala");
    session_start();

    $fecha_actual = date("Y")."-". date("m"). "-". date("d");

    // ------------------- CONEXION SQL-------------------------

    $con = mysqli_connect("localhost", "root", "");
    if(!$con){
        die("Error DB connect ". mysqli_connect_error());
    }

    mysqli_select_db($con, "usuarios2024");
    $con->set_charset("utf8");

    // -------------------------- PETICIONES HTTP -------------------- //


    // -------------------------- PETICION GET ---------------------


    // -------------------------- [GET] LISTA DE USUARIOS ALL ------------------- //

    // Se inicializa un nuevo Metodo HTTP [GET] donde con "==", se hace referencia al Metodo estricto
    // estricto GET
     if($_SERVER["REQUEST_METHOD"] == "GET"){
        // Se crea una nueva peticion [GET]
        $quest = isset($_GET["quest"]) ? $_GET["quest"] : null;
        // Se inicializa la variable $quest que guarda la funcion de "lista_usuarios"
            if($quest == 'lista_usuarios'){ 
                // Consulta a MYSQL
                $mysql = "SELECT * FROM usuarios;";
                // $result guarda y REALIZA la consulta SQL [$mysql]
                $result = mysqli_query($con, $mysql);
                // Ciclo IF donde condiciona a $result para traer los datos de DB en forma de array().
                if($result){

                    $data = array(); 
                    // Ciclo While que asocia y carga las filas con la informacion de la DB.
                    while($row = mysqli_fetch_assoc($result)){
                        $data[] = $row;
                    }
                    // Devuelve la consulta en formato JSON.
                    echo json_encode($data);
                } else{
                    // Si no es posible lo escrito en el if, condiciona a un error Not Found
                    header ("HTTP/1.1 404 Not Found");
                    echo json_encode(array("error" => "No se encontraron datos"));
                }
            }
     }


    // ----------------------- [GET] LISTA USUARIOS FILTRO ----------------------- //

    // Nombre de la funcion con filtro
     if($quest == "lista_usuarios_filtro"){
        $letra = isset($_GET["letra"]) ? $_GET["letra"] : '';   
        // Consulta SQL para obtener datos filtrados por letra. Se usa LIKE y OR para gestionar la consulta.
        $sql = "SELECT * FROM usuarios WHERE id like '%$letra%' OR nombre like '%$letra%' OR usuario like '%$letra%' OR contrasena like '%$letra%'";

        // Estableciendo conexion a DB y a la consulta.
        $result = mysqli_query($con, $sql);

        // Retornando la informacion en un JSON.
        if($result){
            $data = array();
            while($row = mysqli_fetch_assoc($result)){
                $data[] = $row;
            }
            echo json_encode($data);
        } else{
            header ("HTTP/1.1 404 Not Found");
            echo json_encode(array("error" => "No se encontraron datos"));
        }

        $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
        $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";

        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);


        if(password_verify('abc', $hashed_password)){
            echo "Bienvenido!";
        } else{
            echo "Credenciales incorrectas";
        }


     }

     // ------------------------------- [POST] AGREGAR UN NUEVO USUARIO ------------------------------------ //


    if($_SERVER["REQUEST_METHOD"] == "POST"){
        // Decodifica los datosde JSON recibidos
        $data = json_decode(file_get_contents("php://input"), true);
        

        // Verifica si el quest es para ingresar un usuario
        if($data["quest"] = "ingresar_usuario"){
            // Asignacion de variables
            $nombre = isset($data["nombre"]) ? $con->real_escape_string($data["nombre"]) : "";
            $usuario = isset($data["usuario"]) ? $con->real_escape_string($data["usuario"]) : "";
            $contrasena = isset($data["contrasena"]) ? $con->real_escape_string($data["contrasena"]) : "";
            
            // Encriptar contrasena en DB.
            $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

            // Preparacion de consulta para inyectar informacion en la DB.

            $mysql = "INSERT INTO usuarios(nombre, usuario, contrasena) VALUES ('$nombre', '$usuario', '$hashed_password')";

            // Consulta a DB
            $result = mysqli_query($con, $mysql);

            // Obtener el ID que genero INSERT INTO
            $id_generado = mysqli_insert_id($con);

            // Creando nuevo arreglo con los datos insertados.

            $response = array(
                'id' => $id_generado,
                'nombre' => $nombre,
                'usuario' => $usuario,
                'contrasena' => $hashed_password
            );

            // Devolver la respuesta en JSON

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