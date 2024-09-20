<?php

// -------------------- IMPORTACIONES ------------------- //

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


?>  