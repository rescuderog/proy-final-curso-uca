<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Tarea</title>
</head>
<body>
    <form action="/addtask/" method="POST">
        <label for="title">Titulo de la tarea:</label>
        <input type="text" id="title" name="title"><br><br>
        <label for="description">Descripcion de la tarea:</label>
        <input type="text" name="description" id="description"><br><br>
        <label for="completed">Completada:</label>
        <input type="checkbox" name="completed" id="completed"><br><br>
        <input type="submit" value="Crear tarea">
    </form>
</body>
</html>

