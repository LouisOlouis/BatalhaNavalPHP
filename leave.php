<?php
require 'basescripts.php';

$id = intval($_POST['id'] ?? 0);
$player = intval($_POST['player'] ?? 0);

if ($id <= 0 || $player <= 0) {
    http_response_code(204);
    exit;
}

$dirname = "servidor/";
$servername = $dirname . "servidor_" . $id . "/";

if (!is_dir($servername)) {
    http_response_code(204); 
    exit;
}

// atualiza estado do round
write_server($servername, 'LRound', read($servername, 'Round'));
write_server($servername, 'Round', $player . 'L');

http_response_code(204); 
