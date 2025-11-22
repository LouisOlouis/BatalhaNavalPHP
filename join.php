<?php
session_start();
require 'basescripts.php';

// cria pasta
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

// servidor alvo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$servername = $dirname . "servidor_" . $id . ".php";

if (!file_exists($servername)) {
    die("Jogo não encontrado.");
}

// carrega estado
require $servername;

// define player 2
writeserver("Player2", $_SESSION['usuario']);
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
</html>
