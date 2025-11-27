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

//inicio da logica comum
$seuplayer = null;

if (read($servername, 'Player1') == $_SESSION['usuario']) {
    $seuplayer = 1;
    
} else if (read($servername,'Player2') == $_SESSION['usuario']) {
    $seuplayer = 2;
    
} else {
    die('Player inesistente');
}

//logica do leave



$message = '';

//inicio da logica do game
if($seuplayer == 1) {
    $message = 'VOCE E O PLAYER 1';
    write_server($servername, 'Round', 'START');
}
if($seuplayer == 2) {
    $message = 'VOCE E O PLAYER 2';
    write_server($servername,'Round', 'Tab1');
}

if (read($servername, 'Round') === 'START') {
    $message = 'ESPERE O PLAYER 2';
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Game - BatalhaNavalPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Jogo iniciado!</h2>
    <h2>Recarregue a pagina sempre para pegar novas informaçoes do servidor</h2>
<?php
    echo $message;
    ?>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon("leave.php?id=<?= $id ?>&player=<? $seuplayer ?>");
    });
</script>
</html>
