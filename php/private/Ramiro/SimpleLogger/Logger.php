<?php

namespace Ramiro\SimpleLogger;

use DateTime;


/**
 * Clase Logger para loguear facilmente en archivos
 * @package Ramiro\SimpleLogger
 */
class Logger {

    private string $path_to_log;

    /**
     * Constructor: necesita un path de un archivo, que podra tambien crear, si es necesario
     * @param string $path 
     * @return void 
     */
    public function __construct(string $path)
    {
        $this->path_to_log = $path;
    }

    /**
     * Saca el tiempo y fecha directamente en el formato correcto para loguear.
     * @return DateTime 
     */
    protected function get_current_datetime(): string
    {
        $format = "Y-m-d H:i:s";
        $current_dt = new DateTime();
        $dt_string = $current_dt->format($format);
        $timezone = $current_dt->getTimezone()->getName();
        $complete_string = $dt_string.' '.$timezone;
        return $complete_string;
    }

    /**
     * Escribe en el log designado por el path
     * @param string $message 
     * @return bool 
     */
    public function log_into_file(string $message): bool
    {   
        $fp = \fopen($this->path_to_log, 'a+');
        $formatted_dt = $this->get_current_datetime();
        $formatted_message = "[$formatted_dt] ".$message.\PHP_EOL;
        $write = \fwrite($fp, $formatted_message);
        \fclose($fp);
        if($write == false) {
            return false;
        }
        return true;
    }
}