<?php
namespace Ramiro\APIUtils;

use Ramiro\SimpleLogger\Logger;
use CurlHandle;

trait CurlExecuteTrait {
    /**
     * Funcion getter abstracta que la clase debe implementar para asegurarnos que existe el CurlHandle
     * @return CurlHandle 
     */
    abstract protected function _get_curl_obj(): CurlHandle;

    /**
     * Funcion getter abstracta que la clase debe implementar para asegurarnos que existe el Logger
     * @return Logger 
     */
    abstract protected function _get_logger(): Logger;

    /**
     * Funcion getter abstracta que la clase debe implementar para obtener la url y el metodo HTTP
     * @return array 
     */
    abstract protected function _get_url_and_method(): array; 

    /**
     * Chequea que la request de cURL sea exitosa y retorna el json decodeado, de otra forma retorna false.
     * @param string $target_url 
     * @return object|bool 
     */
    private function execute_curl(): object | bool {
        [$target_url, $method] = $this->_get_url_and_method();

        $this->_get_logger()->log_into_file("INFO: Se inicia request hacia $target_url con el metodo $method");
        $resp = curl_exec($this->_get_curl_obj());
        
        if(is_bool($resp)) {
            $this->_get_logger()->log_into_file("ERROR: No se puede establecer una conexión al servidor o excedió el timeout.");
            return false;
        } else {
            $info = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            if (($info == 200) || ($info == 201 && $method == "POST") || ($info == 204)) {
                $resp = json_decode($resp);
                if(!is_null($resp) && !is_bool($resp)) {
                    $this->_get_logger()->log_into_file("ÉXITO: Request exitosa y JSON parseado.");
                    return $resp;
                } else {
                    $this->_get_logger()->log_into_file("ERROR: El server devolvio status 200, pero no devolvio un JSON.");
                    return false;
                }
            }
            else {
                $this->_get_logger()->log_into_file("ERROR: La request ha fallado con código $info.");
                return false;
            }
        }
    }
}