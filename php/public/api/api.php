<?php

require("../../vendor/autoload.php");
require("route_registration_api.php");
//constantes de config
require_once '../config.php';

$db = new \Ramiro\DBManager\Core\Manager(DB_HOST, DB_USER, DB_PASSWORD, DB_SCHEMA);

if($_SERVER["REQUEST_METHOD"] == "PUT" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if($_POST) {
        $router = new \Ramiro\Routing\Router($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], $db->get_obj(), $_POST);
    } else {
        $router = new \Ramiro\Routing\Router($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], $db->get_obj(), json_decode(file_get_contents('php://input'), associative:true));
    }
} else {
    $router = new \Ramiro\Routing\Router($_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"], $db->get_obj());
}
