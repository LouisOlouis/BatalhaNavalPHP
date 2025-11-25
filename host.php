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
$servername = $dirname . "servidor_" . $id . "/";
while (is_dir($servername)) {
    $id += 1;
    $servername = $dirname . "servidor_" . $id . "/";
}

// salva na sessão
if (!isset($_SESSION['serverc'])) {
    $_SESSION['serverc'] = $servername;
    copiar_diretorio('serverbase', $servername);
} else {
    $servername = $_SESSION['serverc'];
}

write_server($servername,'Player1', $_SESSION['usuario']);
$player2message = "Esperando player2...";

$await = true;
$postime = filemtime($servername .'Player2.txt' );
while ($await) {
    if (filemtime($servername .'Player2.txt' ) <= $postime) {
        $player2message = read($servername, 'Player2');
        $await = false;
    }
    sleep(5);
}


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
    <h4><?php echo read($servername,'Player1'); ?></h4>
    <h4><?php echo $player2message; ?></h4>
    <form method="post">
        <button type="submit" name="startgame">Atualizar</button>
    </form>
</body>
</html>
