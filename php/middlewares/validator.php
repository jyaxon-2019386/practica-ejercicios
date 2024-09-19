<?php

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

?>