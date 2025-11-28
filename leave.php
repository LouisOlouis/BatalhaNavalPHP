<?php
require 'basescripts.php';

// cria pasta
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

// servidor alvo
$id = isset($_GET['id']) ;
$servername = $dirname . "servidor_" . $id . "/";

//player alvo
$player = isset($_GET["player"]) ? intval($_GET["player"]) : null;

$liberate = isset($_GET["liberate"]) ? strval($_GET["liberate"]) : true;

echo $player;
echo '<br>';
echo $liberate;
echo '<br>';

if ($player !== null) {
    if ($liberate == 'false') {
        write_server($servername, 'LRound', read($servername, 'Round'));
        write_server($servername, 'Round', $player . 'L');
        echo 'apagado';
    }
}
