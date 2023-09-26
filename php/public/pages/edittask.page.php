<?php
require("../vendor/autoload.php");
use Ramiro\APIUtils\CurlCaller;

$curl = new CurlCaller("http://localhost:80/api/tasks/".$params[1], "GET");
$resp = $curl->execute();
if(is_bool($resp)) {
    echo "Error. No se encuentra el ID especificado.";
    die();
} else {
    $completed = $resp->completed ? "checked" : "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Elemento</title>
</head>
<body>
    <form action="<?php echo "/edittask/".$params[1] ?>" method="POST">
        <label for="title">Titulo de la tarea:</label>
        <input type="text" id="title" name="title" value="<?php echo $resp->title ?>"><br><br>
        <label for="description">Descripcion de la tarea:</label>
        <input type="text" name="description" id="description" value="<?php echo $resp->description ?>"><br><br>
        <label for="completed">Completada:</label>
        <input type="checkbox" name="completed" id="completed" <?php echo $completed ?>><br><br>
        <input type="submit" value="Editar tarea">
    </form>
</body>
</html>