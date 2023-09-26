<?php

namespace Ramiro\TaskManager;

use Ramiro\SimpleLogger\Logger;
use PDO;
use PDOException;

/**
 * Objeto task que representa una tarea
 * @package Ramiro\TaskManager
 */
class Task {
    private ?PDO $handle;
    private ?Logger $logger;

    private ?int $id;
    private string $title;
    private string $description;
    private int $completed;

    private bool $is_new;
    
    /**
     * El constructor espera un $id o el array $data_to_create con el titulo, descripcion y completed bien formateados.
     * Todo el type checking hay que hacerlo del lado de donde se recibe el formulario.
     * @param PDO $handle
     * @param int|null $id 
     * @param array|null $data_to_create 
     * @return false|void 
     */
    public function __construct(PDO $handle, int $id = null, array $data_to_create = null)
    {
        $this->logger = new Logger("/var/www/private/logs/task_log.log");
        $this->handle = $handle;

        if(is_null($id) && !is_null($data_to_create)) {
            $this->is_new = true;
            $this->title = $data_to_create[0];
            $this->description = $data_to_create[1];
            $this->completed = $data_to_create[2] ? 1 : 0;
        } else if (!is_null($id) && is_null($data_to_create)){
            $this->is_new = false;
            $this->id = $id;
        } else {
            $this->logger->log_into_file("Se ha intentado crear un objeto sin ID ni datos para crear el objeto.");
            return false;
        }
    }
    
    /**
     * Si hay un id (lo que significa que la tarea potencialmente existe en la db), se intenta hacer un select.
     * Retorna los datos en un array si es exitoso, y false en cualquier otro caso.
     * @return array|bool 
     */
    public function select_task(): array | bool {
        if(!$this->is_new) {
            $sql = 'SELECT * FROM `tasks` WHERE id=:id LIMIT 1';

            try {
                $stmt = $this->handle->prepare($sql);
                $stmt->execute(array(':id' => $this->id));
                $resp = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $error = "Algo falló durante el select. Mensaje de PDO: ".$e->getMessage();
                $this->logger->log_into_file($error);
                return false;
            }

            if(!is_bool($resp)){
                $this->title = $resp['title'];
                $this->description = $resp['description'];
                $this->completed = $resp['completed'];
                return $resp;
            } else {
                $this->logger->log_into_file("El select del id ".$this->id." no arrojo resultados.");
                return false;
            }
            
        } else {
            $this->logger->log_into_file("Se ha intentado acceder a un id que es nulo. Probablemente no se inicializo el objeto con un valor.");
            return false;
        }
    }

    /**
     * Intenta hacer un insert si hay datos suficientes para hacerlo. Retorna true si fue exitoso, false en cualquier otro caso.
     * @return bool 
     */
    public function add_task(): bool {
        if($this->is_new) {
            if(!$this->title || !$this->description || !is_numeric($this->completed)) {
                $this->logger->log_into_file("Alguno de los campos a agregar esta vacio.");
                return false;
            }

            $sql = "INSERT INTO `tasks` (title, description, completed) VALUES (:title, :description, :completed)";
            try {
                $stmt = $this->handle->prepare($sql);
                $stmt->execute(array(':title' => $this->title,
                                     ':description' => $this->description,
                                     ':completed' => $this->completed));
                $this->id = (int) $this->handle->lastInsertId();
                //seteamos is_new a falso porque ya se creo la tarea, a la vez que trajimos el id anteriormente
                $this->is_new = false;
                return true;
            } catch (PDOException $e) {
                $error = "Algo falló durante el insert. Mensaje de PDO: ".$e->getMessage();
                $this->logger->log_into_file($error);
                return false;
            }  
        } else {
            $this->logger->log_into_file("Se ha intentado agregar una tarea sin los datos necesarios. Se inicializo el objeto con id");
            return false;
        }
    }

    /**
     * Intenta hacer un delete si el id es de un elemento existente. Retorna true si fue exitoso, false en cualquier otro caso.
     * @return bool 
     */
    public function delete_task(): bool {
        if(!$this->is_new) {
            if(!is_bool($this->select_task())) {
                $sql = "DELETE FROM `tasks` WHERE id=:id";
                try {
                    $stmt = $this->handle->prepare($sql);
                    $stmt->execute(array(":id" => $this->id));
                    $this->id = null;
                    $this->is_new = true;
                    return true;
                } catch (PDOException $e) {
                    $error = "Algo falló durante el delete. Mensaje de PDO: ".$e->getMessage();
                    $this->logger->log_into_file($error);
                    return false;
                }
            } else {
                $this->logger->log_into_file("Se intento borrar un elemento con un id inexistente. El id es ".$this->id.".");
                return false;
            }
        } else {
            $this->logger->log_into_file("Se ha intentado borrar una tarea que probablemente tenga id vacio. Se inicializo el objeto con datos");
            return false;
        }
    }

    /**
     * Recibe de 0 a 3 parametros. Si no recibe nada, no modifica nada. Si recibe uno o mas de los 3, intenta modificar tales campos en el row con el id del objeto creado.
     * Si la/s modificacion/es es/son exitosa/s retorna true, en cualquier otro caso, retorna false.
     * Todo esto lo hace adentro de una transaccion, para que si falla una, fallen todas.
     * @param null|string $title 
     * @param null|string $description 
     * @param null|bool $completed 
     * @return bool 
     */
    public function update_task(?string $title = null, ?string $description = null, ?bool $completed = null): bool {
        
        if(is_null($title) && is_null($description) && is_null($completed)) {
            $this->logger->log_into_file("Se invoco el metodo update_task con argumentos nulos.");
            return false;
        }
        if(!$this->id) {
            $this->logger->log_into_file("Se ha intentado hacer un update a un objeto que no tiene id. Probablemente se inicializo con datos.");
            return false; 
        }

        $this->handle->beginTransaction();

        if(!is_null($title)) {
            $sql = "UPDATE `tasks` SET title = :val WHERE id = :id";
            try {
                $stmt = $this->handle->prepare($sql);
                $stmt->execute(array(
                    ":val" => $title,
                    ":id" => $this->id));
            } catch (PDOException $e) {
                $error = "Algo falló durante el update. Mensaje de PDO: ".$e->getMessage();
                $this->logger->log_into_file($error);
                $this->handle->rollBack();
                return false;
            }
        }
        if(!is_null($description)) {
            $sql = "UPDATE `tasks` SET description = :val WHERE id = :id";
            try {
                $stmt = $this->handle->prepare($sql);
                $stmt->execute(array(
                    ":val" => $description,
                    ":id" => $this->id));
            } catch (PDOException $e) {
                $error = "Algo falló durante el update. Mensaje de PDO: ".$e->getMessage();
                $this->logger->log_into_file($error);
                $this->handle->rollBack();
                return false;
            }
        }
        if(!is_null($completed)) {
            $sql = "UPDATE `tasks` SET completed = :val WHERE id = :id";
            try {
                $stmt = $this->handle->prepare($sql);
                $stmt->execute(array(
                    ":val" => $completed,
                    ":id" => $this->id));
            } catch (PDOException $e) {
                $error = "Algo falló durante el update. Mensaje de PDO: ".$e->getMessage();
                $this->logger->log_into_file($error);
                $this->handle->rollBack();
                return false;
            }
        }
        try {
            $this->handle->commit();
            return true;
        } catch (PDOException $e) {
            $error = "Algo falló durante el update. Mensaje de PDO: ".$e->getMessage();
            $this->logger->log_into_file($error);
            $this->handle->rollBack();
            return false;
        }
    }

    /**
     * Metodo estatico que retorna todas las tareas en un array. Toma un solo argumento que es un objeto PDO.
     * @param PDO $dbobj 
     * @return array 
     */
    public static function return_all_tasks(PDO $dbobj): array {
        $logger = new Logger("/var/www/private/logs/task_log.log");
        $sql = 'SELECT * FROM `tasks`';
        try {
            $stmt = $dbobj->prepare($sql);
            $stmt->execute();
            $resp = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resp;
        } catch (PDOException $e) {
            $error = "Algo falló durante el select. Mensaje de PDO: ".$e->getMessage();
            $logger->log_into_file($error);
            return false;
        }
    }

    /**
     * Metodo estatico para chequear si la tarea existe o no, retorna true si el select fue exitoso, false en cualquier otro caso.
     * @param PDO $dbobj 
     * @param int $id 
     * @return bool 
     */
    public static function task_exists(PDO $dbobj, int $id): bool {
        $logger = new Logger("/var/www/private/logs/task_log.log");
        $sql = 'SELECT * FROM `tasks` WHERE id=:id LIMIT 1';

        try {
            $stmt = $dbobj->prepare($sql);
            $stmt->execute(array(':id' => $id));
            $resp = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Algo falló durante el select. Mensaje de PDO: ".$e->getMessage();
            $logger->log_into_file($error);
            return false;
        }

        if(!is_bool($resp)){
            return true;
        } else {
            $logger->log_into_file("El select del id ".$id." no arrojo resultados.");
            return false;
        }
    }
}