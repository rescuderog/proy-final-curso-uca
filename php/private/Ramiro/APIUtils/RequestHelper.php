<?php

namespace Ramiro\APIUtils;

/**
 * Funciones helper para evitar repetir codigo a futuro
 * @package Ramiro\APIUtils
 */
class RequestHelper {
    /**
     * Genera una respuesta en formato JSON (con los headers apropiados), encodeando un array que se le pase como parámetro.
     * Asimismo, se le puede pasar un status code (cuando no sea el default, 200).
     * @param array $array 
     * @param int $status_code 
     * @return void 
     */
    public static function generar_respuesta_json(array $array, int $status_code = 200) {
        http_response_code($status_code);
        $enc_array = json_encode($array);
        header("Content-Type: application/json");
        echo $enc_array;
    }

    /**
     * Retorna true si el regex matchea con la string a validar, false en cualquier otro caso.
     * @param string $a_validar 
     * @param string $validador 
     * @return int|false 
     */
    public static function validar_regex(string $a_validar, string $validador) {
        return preg_match($validador, $a_validar);
    }
}