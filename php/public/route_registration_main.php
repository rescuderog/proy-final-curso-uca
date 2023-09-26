<?php

use Ramiro\Routing\Router;
use Ramiro\APIUtils\RequestHelper;

Router::agregar_ruta("GET", 
                     "index", 
                     function(array $params, PDO $db) {
                        include "./pages/index.page.php";
                     });

Router::agregar_ruta("POST", 
                     "index", 
                     function(array $params, PDO $db, array $post_field) {
                        include "./pages/index.page.php";
                     });

Router::agregar_ruta("GET", 
                     "edittask", 
                     function(array $params, PDO $db) {
                        if(!empty($params[1])) {
                            if(RequestHelper::validar_regex($params[1], "/^[0-9]+$/")) {
                                include "./pages/edittask.page.php";
                            } else {
                                echo "No se ha ingresado un valor numerico, intenta de nuevo.";
                            }
                        } else {
                            echo "No se ha ingresado un valor. Intenta de nuevo.";
                        }
                    });

Router::agregar_ruta("POST", 
                     "edittask", 
                     function(array $params, PDO $db, array $post_field) {
                        if(!empty($params[1])) {
                            if(RequestHelper::validar_regex($params[1], "/^[0-9]+$/")) {
                                include "./pages/processedit.page.php";
                            } else {
                                echo "No se ha ingresado un valor numerico, intenta de nuevo.";
                            }
                        } else {
                            echo "No se ha ingresado un valor. Intenta de nuevo.";
                        }
                    });

Router::agregar_ruta("GET", 
                     "addtask", 
                     function(array $params, PDO $db) {
                        include "./pages/addtask.page.php";
                     });

Router::agregar_ruta("POST", 
                     "addtask", 
                     function(array $params, PDO $db, array $post_field) {
                        include "./pages/processadd.page.php";
                     });

Router::agregar_ruta("GET", 
                     "deletetask", 
                     function(array $params, PDO $db) {
                        include "./pages/deletetask.page.php";
                     });