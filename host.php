<?php 
session_start();
require 'basescripts.php';

// cria a pasta de servidores
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

// encontra nome livre
$id = 1;
$servername = $dirname . "servidor_" . $id . ".php";
while (file_exists($servername)) {
    $id += 1;
    $servername = $dirname . "servidor_" . $id . ".php";
}

// salva na sessão
if (!isset($_SESSION['serverc'])) {
    $_SESSION['serverc'] = $servername;
} else {
    $servername = $_SESSION['serverc'];
}

// cria arquivo base
file_put_contents($servername, file_get_contents('serverbase.php'));

// carrega dados
require $servername;

// define player 1
writeserver("Player1", $_SESSION['usuario'], $servername);
require $servername;

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
    <h4><?php echo $serverinfo['Player1']; ?></h4>
    <h4><?php echo $serverinfo['Player2'] ?? "Aguardando jogador 2..."; ?></h4>
    <form method="post">
        <button type="submit" name="startgame">Atualizar</button>
    </form>
</body>
<!--<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon("delete.php");
    });
</script>-->
</html>
