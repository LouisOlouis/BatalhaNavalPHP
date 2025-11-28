<?php 
session_start();
require 'basescripts.php';

if (!isset($_SESSION['usuario'])) {
    die("Usuário não logado.");
}

// cria a pasta de servidores
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

// encontra nome livre
$id = 1;
$servername = $dirname . "servidor_" . $id . "/";



// salva na sessão e cria o servidor
if (!isset($_SESSION['serverc'])) {
        while (is_dir($servername)) {
            $id++;
            $servername = $dirname . "servidor_" . $id . "/";
        }
    $_SESSION['serverc'] = $servername;
    copiar_diretorio('serverbase', $servername);
} else {
    $servername = $_SESSION['serverc'];
}

if (!is_dir($servername)) {
    session_destroy();
    die("Erro ao criar servidor.");
}

write_server($servername,'Player1', $_SESSION['usuario']);

$liberate = 'false';

if (read($servername,'Round') == '1L') {
    write_server($servername,'Round', 'NULL');
    $liberate = 'true';
    echo "ping";
}

$disabled = 'disabled';

if (read($servername, 'Round') == '2L') {
    $disabled = 'disabled';
    write_server($servername,'Player2', 'NULL');
    $player2message = "Esperando player2...";
}



$player2message = "Esperando player2...";
if (read($servername, 'Player2') !== 'NULL') {
    $player2message = read($servername, 'Player2');
    $disabled = '';
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['startgame'])) {
    if ($disabled == '') {
        if (read($servername, 'Player2') === 'NULL') {
            die("Não é possível iniciar o jogo sem o Player 2.");
        }
        write_server($servername, 'Round', 'START');
        header("Location: game.php?id=" . $id);
        exit();
    }
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
    <h4><?php echo read($servername,'Player1') ?? "Jogador 1 saiu :("; ?></h4>
    <h4><?php echo $player2message; ?></h4>
    <br>
    <form method="post">
        <button type="submit" <?php echo $disabled ?> name="startgame">Iniciar Jogo</button>
    </form>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(`leave.php?id=<?= $id ?>&player=1&liberate=<?= $liberate ?>`);
    });
</script>
</html>
