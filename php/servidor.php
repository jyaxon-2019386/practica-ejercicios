<?php
    // -------------------- CONFIGS ----------------------------

    define("CHARSET", "UFT-8");
    header("Content-Type: text/html; charset=UTF-8;");
    header("Access-Control-Allow-Origin: *");
    date_default_timezone_set("UTC");
    date_default_timezone_set("America/Guatemala");
    session_start();

    $fecha_actual = date("Y")."-". date("m"). "-". date("d");

    // ------------------- CONEXION SQL-------------------------

    $con = mysqli_connect("localhost", "admin", "");
    if(!$con){
        die("Error DB connect ". mysqli_connect_error());
    }

    mysqli_select_db($con, "usuarios2024");
    $con->set_charset("utf8");

    // -------------------------- PETICION HTTP -------------------- //


    // -------------------------- PETICION GET ---------------------

    // if($_SERVER["REQUEST_METHOD"] == "GET"){
    //     $quest = isset($_GET["quest"]) ? $_GET["quest"] : null;
    //         if($quest == 'lista_usuarios'){ 
    //             echo 'Connected succesfully'
    //         }
    // }
    

    // -------------------------- PETICION GET ---------------------

    // Selecciona todos los campos de la Tabla Usuarios
    $sql = "SELECT * FROM usuarios";

    // Realiza la consulta a la db argumentando la conexion y la consula $sql
    $result = mysqli_query($con, $sql);


    // Se obtienen las filas de la tabla Usuarios solo si es mayor a 0, de lo contrario imprime 0 results.
    if (mysqli_num_rows($result) > 0) {
        // Ciclo While para obtener la tabla Usuarios como un Array.
        while($request = mysqli_fetch_assoc($result)) {
            // Imprime los campos de los usuarios exceptuando la contrasena por seguridad.
            echo $request["id"]. " " . $request["nombre"]. " " . $request["usuario"]"\n";
            }
        } else {
            echo "0 results";
        }


    // $request = mysqli_num_rows($result) > 0 ? mysqli_query($con, $sql) : null;
   
    // $request = mysqli_fetch_assoc($result);
    //     echo $request["id"]. " " . $request["nombre"]. " " . $request["usuario"];
    
?>