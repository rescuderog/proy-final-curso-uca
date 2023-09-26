<?php

namespace Ramiro\APIUtils;

use CurlHandle;
use Ramiro\SimpleLogger\Logger;

/**
 * Clase simple que implementa los traits que preparan y ejecutan una request de Curl
 * @package Ramiro\APIUtils
 */
class CurlCaller {
    use CurlExecuteTrait;
    use CurlPrepareTrait;

    protected CurlHandle $curl;
    protected Logger $logger;
    protected string $url;
    protected string $method;
    protected ?array $params = null;
    protected bool $has_been_used = false;
    
    /**
     * El constructor setea todas las variables internas y deja listo para hacer el curl_prepare y curl_execute.
     * @param string $url 
     * @param string $method 
     * @param null|string $params 
     * @return void 
     */
    public function __construct(string $url, string $method, ?array $params = null)
    {
        $this->logger = new Logger("/var/www/private/logs/curl_log.log");
        $this->curl = curl_init();
        $this->method = $method;
        $this->url = $url;
        $this->params = $params;
    }

    /**
     * Prepara y ejecuta la sentencia de curl, retorna el json de execute_curl si es exitoso o false en cualquier otro caso.
     * @return object|bool 
     */
    public function execute(): object | bool {
        $prepared = $this->prepare_session();
        if(!$prepared) {
            $this->logger->log_into_file("Fallo al preparar la request curl a ".$this->url." con el metodo ".$this->method);
            return false;
        }

        $executed = $this->execute_curl();
        if(is_bool($executed)) {
            $this->logger->log_into_file("Fallo al ejecutar la request curl a ".$this->url." con el metodo ".$this->method);
            return false;
        }

        $this->has_been_used = true;
        return $executed;
    }

    /**
     * Retorna el status code de la ultima sentencia curl ejecutada y falso si nunca se ejecuto alguna sentencia.
     * @return int|bool 
     */
    public function get_status_code(): int | bool {
        if($this->has_been_used) {
            return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        } else {
            return false;
        }
    }

    protected function _get_curl_obj(): CurlHandle
    {
        return $this->curl;
    }

    protected function _get_logger(): Logger
    {
        return $this->logger;
    }

    protected function _get_url_and_method(): array
    {
        return [$this->url, $this->method];
    }

    protected function _get_post_field(): ?array
    {
        return $this->params;   
    }
}