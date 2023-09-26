<?php
use Ramiro\APIUtils\CurlCaller;

$id = $params[1];

$curl = new CurlCaller("http://localhost:80/api/tasks/".$id, "GET");
$resp = $curl->execute();
if(is_bool($resp)) {
    echo "Error. No se encuentra el ID especificado.";
    die();
}

if($post_field["completed"] == "on") {
    $completed = true;
} else {
    $completed = false;
}

$params = array("title" => $post_field["title"],
                "description" => $post_field["description"],
                "completed" => $completed);

$curl_put = new CurlCaller("http://localhost:80/api/tasks/".$id, "PUT", $params);
$resp_put = $curl_put->execute();

if(!is_bool($curl_put->get_status_code())) {
    if($curl_put->get_status_code() == 200) {
        $message = "Se ha modificado el elemento con id ".$id." correctamente.";
    } else {
        $message = "No se ha podido modificar el elemento. Intente nuevamente.";
    }
} else {
    $message = "No se ha podido modificar el elemento. Intente nuevamente";
}

?>

<form id="auto-submit" action="/index" method="POST">
    <input type="hidden" name="message" value="<?php echo $message; ?>">
</form>

<script type="text/javascript">
    document.getElementById('auto-submit').submit();
</script>