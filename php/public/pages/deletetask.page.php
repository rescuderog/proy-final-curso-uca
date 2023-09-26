<?php
use Ramiro\APIUtils\CurlCaller;

$id = $params[1];

$curl_put = new CurlCaller("http://localhost:80/api/tasks/".$id, "DELETE");
$resp_put = $curl_put->execute();
$status_code = $curl_put->get_status_code();

if(!is_bool($status_code)) {
    if($status_code == 200) {
        $message = "Se ha removido el elemento con id ".$id." correctamente.";
    } else {
        $message = "No se ha podido remover el elemento. Intente nuevamente.";
    }
} else {
    $message = "No se ha podido remover el elemento. Intente nuevamente";
}

?>

<form id="auto-submit" action="/index" method="POST">
    <input type="hidden" name="message" value="<?php echo $message; ?>">
</form>

<script type="text/javascript">
    document.getElementById('auto-submit').submit();
</script>