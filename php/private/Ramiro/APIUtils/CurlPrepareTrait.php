<?php
namespace Ramiro\APIUtils;

use Ramiro\SimpleLogger\Logger;
use CurlHandle;

trait CurlPrepareTrait {
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
     * Funcion getter abstracta que la clase debe implementar para obtener el post field, si lo hubiera
     * @return array 
     */
    abstract protected function _get_post_field(): array | null;

    /**
     * Setea el metodo y la url en la sesion de curl. Si es POST, setea el post_field tambien.
     * @param string $method 
     * @param string $url 
     * @param null|array $post_field 
     * @return false|void 
     */
    protected function prepare_session(){
        //seteando parametros generales
        curl_setopt_array($this->_get_curl_obj(), 
                          array(
                            CURLOPT_TIMEOUT => 3,
                            CURLOPT_RETURNTRANSFER => true
                          ));

        $post_field = $this->_get_post_field();
        [$url, $method] = $this->_get_url_and_method();
        
        //hace un early return si no hay campo de POST en una request POST, logueando el incidente
        if($method == "POST" && is_null($post_field)) {
            $this->_get_logger()->log_into_file("Se ha intentado hacer un POST a la URL $url sin datos en el POSTFIELD.");
            return false;
        } else if($method == "PUT" && is_null($post_field)) {
            $this->_get_logger()->log_into_file("Se ha intentado hacer un PUT a la URL $url sin datos en el POSTFIELD.");
            return false;
        }

        curl_setopt($this->_get_curl_obj(), CURLINFO_HEADER_OUT, true);

        match($method) {
            "GET" => curl_setopt_array($this->_get_curl_obj(), 
                                        array(
                                            CURLOPT_URL => $url,
                                            CURLOPT_HTTPGET => true,
                                        )),
            "POST" => curl_setopt_array($this->_get_curl_obj(),
                                        array(
                                            CURLOPT_URL => $url,
                                            CURLOPT_POST => true,
                                            CURLOPT_POSTFIELDS => json_encode($post_field),
                                            CURLOPT_HTTPHEADER => array(
                                                'Content-Type: application/json'
                                            )
                                        )),
            "PUT" => curl_setopt_array($this->_get_curl_obj(),
                                       array(
                                            CURLOPT_URL => $url,
                                            CURLOPT_CUSTOMREQUEST => "PUT",
                                            CURLOPT_POSTFIELDS => json_encode($post_field),
                                            CURLOPT_HTTPHEADER => array(
                                                'Content-Type: application/json' 
                                            )
                                        )),
            "DELETE" => curl_setopt_array($this->_get_curl_obj(),
                                          array(
                                            CURLOPT_URL => $url,
                                            CURLOPT_CUSTOMREQUEST => "DELETE"
                                          ))
        };

        return true;
    }
}