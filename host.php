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

$desativar = true;
$disabled = $desativar ? 'disabled' : '';

$player2message = "Esperando player2...";
if (read($servername, 'Player2') !== 'NULL') {
    $player2message = read($servername, 'Player2');
    $desativar = false;
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
    <h2>Recarregue a pagina sempre para pegar novas informaçoes do servidor</h2>
    <h3>Jogadores:</h3>
    <h4><?php echo read($servername,'Player1'); ?></h4>
    <h4><?php echo $player2message; ?></h4>
    <br>
    <form method="post">
        <button type="submit" <?php echo $disabled ?> name="startgame">Iniciar Jogo</button>
    </form>
</body>
</html>
