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

$message = '';

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
    if (read($servername, 'Round') === 'START') {
        echo $message;
    }
