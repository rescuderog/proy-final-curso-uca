<?php

namespace Ramiro\DBManager\Core;

use PDO, PDOException;
use Ramiro\SimpleLogger\Logger;

/**
 * Clase manejadora de la conexion a la DB
 * @package Ramiro\DBManager\Core
 */
class Manager {

    private ?PDO $pdo_obj = null;
    private Logger $logger;

    /**
     * Constructor: pide los datos necesarios para conectarse a la DB.
     * @param string $host 
     * @param string $user 
     * @param string $password 
     * @param string $schema 
     * @param int $port 
     * @return void 
     */
    public function __construct(string $host, string $user, string $password, string $schema, int $port=3306)
    {
        $dsn = "mysql:host=$host:$port;dbname=$schema";
        $this->logger = new Logger("/var/www/private/logs/db.log");
        try {
            $this->pdo_obj = new PDO($dsn, $user, $password);
            //esto seteamos para poder hacer los try-catch de forma simple
            $this->pdo_obj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->pdo_obj = null;
            $this->logger->log_into_file($e->getMessage());
        }
    }

    /**
     * Getter del atributo pdo_obj, valida si esta creado
     * @return PDO|false 
     */
    public function get_obj() {
        if(! \is_null($this->pdo_obj)) {
            return $this->pdo_obj;
        } else {
            return false;
        }
    }
}