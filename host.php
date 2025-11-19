<?php 
session_start();

//cria servidor
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

$id = 1;
$servername = $_SERVER['DOCUMENT_ROOT'] . '/' . $dirname . "servidor_" . $id . ".php";
while (file_exists($servername)) {
    $id += 1;
    $servername = $_SERVER['DOCUMENT_ROOT'] . '/' . $dirname . "servidor_" . $id . ".php";
}

if (!isset($_SESSION['serverc'])) {
   $_SESSION['serverc'] = $servername;
} else {
    $servername = $_SESSION['serverc'];
}

$fp = fopen($servername,'w');



?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Host - BatalhaNavalPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Você está hospedando um jogo!</h1>
    <h3>Jogadores:</h3>
    <h4><?php echo $_SESSION['usuario']; ?></h4>
</body>
<script>
window.addEventListener("beforeunload", function () {
    navigator.sendBeacon("delete.php");
});
</script>
</html>