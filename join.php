<?php
session_start();
require 'basescripts.php';

//cria a pasta de servidores, se não existir
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

//encontra o nome do servidor entrado
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$servername = $dirname . "servidor_" . $id . ".php";

if (!file_exists($servername)) {
    die("Jogo não encontrado.");
}

//pucha o server
require $servername;

//coloca o jogador 2 como o usuario
writeserver("Player2",$_SESSION['usuario']);
require $servername;


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Join - BatalhaNavalPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Você está em um jogo!</h1>
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