<?php
    // --------- VALIDACIONES DE CARACTERES INPUTS --------- //

    function validar_usuario($res){
        return preg_match("/^[a-zA-Z0-9_]{3,20}$/", $res);
    }

    function validar_nombre($res){
        return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,50}$/", $res);
    }

    function validar_contrasena($res){
        return preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,64}$/", $res);
    }

    // Funciones para UPDATE

    function validar_contrasena_nueva($res) {
        return empty($res) || preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,64}$/", $res);
    }
    
    function validar_repetir_contrasena_nueva($res) {
        return empty($res) || preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,64}$/", $res);
    }
?>