<?php

use Ramiro\Routing\Router;
use Ramiro\APIUtils\RequestHelper;
use Ramiro\TaskManager\Task;

Router::agregar_ruta("GET", 
                    "tasks", 
                    function(array $params, PDO $db) {
                        if(!empty($params[1])) {
                            if(RequestHelper::validar_regex($params[1], "/^[0-9]+$/")) {
                                $task = new Task($db, id:(int) $params[1]);
                                $data = $task->select_task();
                                if(!is_bool($data)) {
                                    RequestHelper::generar_respuesta_json($data);
                                } else {
                                    $error = array("error" => "No se encontro el elemento con id pedido. Intente nuevamente.");
                                    RequestHelper::generar_respuesta_json($error, 404);
                                }
                            } else {
                                $error = array("error" => "No ha introducido un valor numerico. Intente nuevamente.");
                                RequestHelper::generar_respuesta_json($error, 400);
                            }
                        } else {
                            $tasks = Task::return_all_tasks($db);
                            RequestHelper::generar_respuesta_json($tasks);
                        }
                    });

Router::agregar_ruta("POST",
                     "tasks",
                     function(array $params, PDO $db, array $post_field) {
                        $data = [$post_field['title'], $post_field['description'], $post_field['completed']];
                        $task = new Task($db, data_to_create:$data);
                        $resp = $task->add_task();
                        if($resp) {
                            $datos_en_db = $task->select_task();
                            RequestHelper::generar_respuesta_json(array("exito" => "Se ha creado el elemento correctamente",
                                                                        "datos_creados" => $datos_en_db), 201);
                        } else {
                            $error = array("error" => "No se pudo crear el elemento. Intente nuevamente.");
                            RequestHelper::generar_respuesta_json($error, 404);
                        }
                     });

Router::agregar_ruta("PUT",
                     "tasks",
                     function(array $params, PDO $db, array $post_field) {
                        if(!empty($params[1])) {
                            if(RequestHelper::validar_regex($params[1], "/^[0-9]+$/")) {
                                $id = $params[1];
                                if(Task::task_exists($db, $id)) {
                                    $task = new Task($db, id:$id);
                                    $resp = $task->update_task(title: $post_field['title'], description: $post_field['description'], completed:$post_field['completed']);
                                    if($resp) {
                                        $datos_en_db = $task->select_task();
                                        RequestHelper::generar_respuesta_json(array("exito" => "Se ha modificado el elemento correctamente",
                                                                                    "datos_editados" => $datos_en_db));
                                    } else {
                                        $error = array("error" => "No se pudo editar el elemento. Intente nuevamente.");
                                        RequestHelper::generar_respuesta_json($error, 404);
                                    }
                                }
                                else {
                                    $error = array("error" => "No se pudo encontrar un elemento con ese id. Intente nuevamente.");
                                    RequestHelper::generar_respuesta_json($error, 404); 
                                }
                            } else {
                                $error = array("error" => "El id ingresado no es numerico. Intente nuevamente.");
                                RequestHelper::generar_respuesta_json($error, 404);
                            } 
                        } else {
                            $error = array("error" => "No ha ingresado un id. Intente nuevamente.");
                            RequestHelper::generar_respuesta_json($error, 404); 
                        }
                    });

Router::agregar_ruta("DELETE",
                     "tasks",
                     function(array $params, PDO $db) {
                        if(!empty($params[1])) {
                            if(RequestHelper::validar_regex($params[1], "/^[0-9]+$/")) {
                                $id = $params[1];
                                if(Task::task_exists($db, $id)) {
                                    $task = new Task($db, id:$id);
                                    $resp = $task->delete_task();
                                    if($resp) {
                                        RequestHelper::generar_respuesta_json(array("exito" => "Se ha removido el elemento correctamente"), 200);
                                    } else {
                                        $error = array("error" => "No se pudo remover el elemento. Intente nuevamente.");
                                        RequestHelper::generar_respuesta_json($error, 404);
                                    }
                                }
                                else {
                                    $error = array("error" => "No se pudo encontrar un elemento con ese id. Intente nuevamente.");
                                    RequestHelper::generar_respuesta_json($error, 404); 
                                }
                            } else {
                                $error = array("error" => "El id ingresado no es numerico. Intente nuevamente.");
                                RequestHelper::generar_respuesta_json($error, 404);
                            } 
                        } else {
                            $error = array("error" => "No ha ingresado un id. Intente nuevamente.");
                            RequestHelper::generar_respuesta_json($error, 404); 
                        }
                    });