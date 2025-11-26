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
$servername = $dirname . "servidor_" . $id . "/";

if (!is_dir($servername)) {
    die("Jogo não encontrado.");
}

write_server($servername,'Player2', $_SESSION['usuario']);

if (read($servername, 'Round') === 'START') {
    write_server($servername,'Round', 'Tab1');
    header("Location: game.php?id=" . $id);
    exit();
}

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
    <h2>Recarregue a pagina sempre para pegar novas informaçoes do servidor</h2>
    <h3>Jogadores:</h3>
    <h4><?php echo read($servername,'Player1'); ?></h4>
    <h4><?php echo read($servername,'Player2') ?? "Aguardando jogador 2..."; ?></h4>
</body>
</html>
