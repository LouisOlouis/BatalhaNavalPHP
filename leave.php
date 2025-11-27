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



if ($player !== null) {
    write_server($servername, 'Round', $player . 'L');
}
