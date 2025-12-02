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

$player2 = read($servername, 'Player2');

if ($player2 !== 'NULL' && $player2 !== $_SESSION['usuario']) {
    die('Ja existe um player2 nessa sessao');
}

write_server($servername,'Player2', $_SESSION['usuario']);

$liberate = 'false';


if (read($servername,'Round') == '2L') {
    echo "ping";
    write_server($servername,'Round', read($servername,'LRound'));
}

if (read($servername, 'Round') == '1L') {
    die('player 1 saiu');
}

if (read($servername, 'Round') == 'START') {
    write_server($servername,'Round', 'Tab1');
    write_server($servername,'LRound', 'Tab1');
    $TABULEIRO = make_board();
    write_server($servername, 'Tab2', serialize($TABULEIRO));
    write_server($servername, 'TabR2', serialize($TABULEIRO));
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
    <h4><?php echo read($servername,'Player1') ?? "Jogador 1 saiu :("; ?></h4>
    <h4><?php echo read($servername,'Player2') ?? "Aguardando jogador 2..."; ?></h4>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(`leave.php?id=<?= $id ?>&player=2&liberate=<?= $liberate ?>`);
    });
</script>
</html>
