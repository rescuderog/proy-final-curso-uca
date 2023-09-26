<?php

namespace Ramiro\Routing;

use Ramiro\SimpleLogger\Logger;
use Ramiro\APIUtils\RequestHelper;
use Closure, PDO;

/**
 * Clase que permite rutear las requests a partir de rutas con slashes (por ej. /api/hola/1).
 * @package Ramiro\APIUtils
 */
class Router {
    private ?Logger $logger = null;
    private ?PDO $handle = null;
    private static array $rutas = array();
    private ?array $post_field;

    /**
     * Toma una URI y un metodo HTTP, limpia la url y pasa a validar la ruta contra una lista de rutas aceptadas.
     * Si no encuentra la ruta, retorna 404.
     * Asimismo, si la variable $db no es nula, crea el objeto PDO correspondiente para pasar a las rutas.
     * @param string $uri 
     * @param string $method 
     * @return void 
     */
    public function __construct(string $uri, string $method, PDO $db = null, ?array $post_field = array())
    {
        $this->logger = new Logger("/var/www/private/logs/router_log.log");
        if(!is_null($db)) {
            $this->handle = $db;
        }

        if($post_field) {
            $this->post_field = $post_field;
        }
        
        if($uri == "/") {
            $uri = ['index'];
        } else {
            //si hay alguna extension .php, la sacamos, primero de todo
            $uri = str_replace(".php", "", $uri);
            //borramos el ultimo slash si es que hubiera y partimos la string por sus slashes
            $uri = rtrim($uri, '/');
            $uri = explode('/', $uri);
            //limpiamos el primer elemento (vacio) y el segundo (la ruta inicial /api/ que no nos sirve, si se trata de una llamada a la api)
            unset($uri[0]);
            if($uri[1] == "api") {
                unset($uri[1]);
            }
            //reordenamos los elementos luego de los unset
            $uri = array_values($uri);
        }
        
        //llamamos finalmente al validador de ruta
        if(!$this->validar_ruta($uri, $method)) {
            $error = array("error" => "No se encuentra esta pagina o ha ocurrido un error, intente nuevamente.");
            RequestHelper::generar_respuesta_json($error, 404);
        }
    }

    /**
     * Metodo estatico para agregar una ruta al array de keys que contiene el Router. Toma como argumentos una func anonima con la logica,
     * el metodo (GET, POST, PUT, etc) y una ruta base. Si se toma mas de un slash, se debe chequear dentro del closure que se pasa.
     * Todo closure que se pase necesita tener un parametro de tipo array, de forma que se puedan pasar param adicionales, y un parametro adicional
     * que es usado para pasar el objeto PDO de la DB si lo hubiera.
     * @param Closure $fn
     * @param string $metodo 
     * @param string $ruta 
     * @return void 
     */
    public static function agregar_ruta(string $metodo, string $ruta, Closure $fn): void {
        self::$rutas[$ruta.$metodo] = array("function" => $fn,
                                            "metodo" => $metodo,
                                            "ruta" => $ruta);
    }

    /**
     * Valida la ruta ingresada por el usuario, junto con el metodo HTTP por el que vino la request. Da true y ejecuta lo contenido en el closure
     * si existe. De no existir retorna false para que el constructor maneje el 404. Si el metodo es POST o PUT, tambien busca agrega la superglobal $_POST
     * @param array $nombre_ruta 
     * @param string $metodo 
     * @return bool 
     */
    private function validar_ruta(array $nombre_ruta, string $metodo) {
        if(array_key_exists($nombre_ruta[0].$metodo, self::$rutas)) {
            if($metodo == "POST" || $metodo == "PUT") {
                if(!$this->post_field) {
                    $this->logger->log_into_file("Se intento acceder a la ruta ".$nombre_ruta[0]." con el metodo ".$metodo." con la superglobal POST vacia");
                    return false;
                }
                self::$rutas[$nombre_ruta[0].$metodo]["function"]($nombre_ruta, $this->handle, $this->post_field);
            } else {
                self::$rutas[$nombre_ruta[0].$metodo]["function"]($nombre_ruta, $this->handle);
            }
            return true;
        } else {
            $this->logger->log_into_file("Se intento acceder a la ruta ".$nombre_ruta[0]." pero esta no existe");
            return false;
        }
    }
}