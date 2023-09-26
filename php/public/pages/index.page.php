<?php

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST["message"];
} else {
    $message = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Tareas</title>
</head>
<body>
    <?php
    if(!is_null($message)) {
        echo "<p>".$message."</p>";
    }
    ?>
    <p><a href="addtask.php">Agregar Tarea</a></p>
    <table>
        <thead>
            <th>ID</th>
            <th>Tarea</th>
            <th>Descripcion de la tarea</th>
            <th>Completada</th>
            <th>Editar</th>
            <th>Eliminar</th>
        </thead>
        <tbody>
            <?php
                $data = json_decode(file_get_contents("http://localhost:80/api/tasks"));
                foreach($data as &$row) {
                    $completed = $row->completed ? "Si" : "No";
                    $edit_link = "edittask/".$row->id;
                    $delete_link = "deletetask/".$row->id;

                    echo "<tr>";
                    echo "<td>".$row->id."</td>";
                    echo "<td>".$row->title."</td>";
                    echo "<td>".$row->description."</td>";
                    echo "<td>".$completed."</td>";
                    echo "<td><a href='".$edit_link."'> Editar </a></td>";
                    echo "<td><a href='".$delete_link."'> Borrar </a></td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>
</body>
</html>