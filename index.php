<?php 
session_start();
require 'basescripts.php';

$base = 'servidor/';
$limite = time() - (10 * 60);

$pastas = glob($base . '*', GLOB_ONLYDIR);

foreach ($pastas as $pasta) {
    $mtime = filemtime($pasta);

    if ($mtime < $limite) {
        if (read($pasta, 'Player1') == $_SESSION['usuario']) {
            session_unset();
            echo "Sua sessão expirou por inatividade.<br>";
        }
        delete_server($pasta);
        echo "Pasta removida totalmente: $pasta<br>";
    }
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['player_name'])) {
        $_SESSION['usuario'] = $_POST['player_name'];

        if (isset($_POST['host'])) {
            header('Location: host.php');
            exit;

        } elseif (isset($_POST['join'])) {
            if (!empty($_POST['game_id'])) {
                $game_id = $_POST['game_id'];
                header('Location: join.php?id=' . $game_id);
                exit;
            }
        }

    } else {
        $mensagem = 'Insira um nome de usuario';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>BatalhaNavalPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Batalha Naval em PHP</h1>
    <h3>Feito por louis_louis</h3>
    <form method="post">
        <h3>Entre:</h3>
        <input type="text" name="player_name" placeholder="Seu nome" autocomplete="off" required>
        <br><br>
        <button type="submit" name="host">Entrar como host</button>
        <br><br>
        <input type="number" name="game_id" autocomplete="off" placeholder="ID do jogo">
        <button type="submit" name="join">Entrar como jogador</button>
    </form>
    <h4><?php echo $mensagem; ?></h4>
</body>
</html>
