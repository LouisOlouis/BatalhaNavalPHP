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

// identificação do player
$seuplayer = null;

if (read($servername, 'Player1') == $_SESSION['usuario']) {
    $seuplayer = 1;
} else if (read($servername,'Player2') == $_SESSION['usuario']) {
    $seuplayer = 2;
} else {
    die('Player inexistente');
}

// lógica do leave
$liberate = 'false';

if ($seuplayer == 1 && read($servername,'Round') == '1L') {
    write_server($servername, 'Round', read($servername,'LRound'));
    $liberate = 'true';
}

if ($seuplayer == 2 && read($servername,'Round') == '2L') {
    write_server($servername, 'Round', read($servername,'LRound'));
    $liberate = 'true';
}

if (read($servername,'Round') == '1L' || read($servername,'Round') == '2L') {
    die('dead server error :(');
}

$pmessage = '';
$message = "";
$block = false;

// leitura segura do tabuleiro
$raw = read($servername, 'Tab'.$seuplayer);
$TABULEIRO = $raw ? @unserialize($raw) : null;

if (!is_array($TABULEIRO)) {
    $TABULEIRO = array_fill(0, 6, array_fill(0, 6, '-'));
}

// leitura do POST
$pi = null;
$pj = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pos'])) {
    list($pi, $pj) = array_map('intval', explode('-', $_POST['pos']));
}

// contador de barcos
function count_boats(array $tab) {
    $c = 0;
    foreach ($tab as $row) {
        foreach ($row as $cell) {
            if ($cell === 'B') $c++;
        }
    }
    return $c;
}

$pmessage = "VOCÊ É O PLAYER ".$seuplayer;

// ---------------------------
// FASE TAB1 (PLAYER 1 COLOCA BARCOS)
// ---------------------------
if (read($servername,'Round') == 'Tab1') {
    $message = "Jogador 1 coloque 3 barcos";

    if ($seuplayer == 1) {
        if ($pi !== null && isset($TABULEIRO[$pi][$pj]) && $TABULEIRO[$pi][$pj] === '-') { // <- correção
            $TABULEIRO[$pi][$pj] = 'B';
            write_server($servername, 'Tab1', serialize($TABULEIRO));
        }

        if (count_boats($TABULEIRO) >= 3) {
            write_server($servername, 'Round', 'Tab2');
            write_server($servername, 'LRound', 'Tab2');
        }
    } else $block = true;
}

// ---------------------------
// FASE TAB2 (PLAYER 2 COLOCA BARCOS)
// ---------------------------
if (read($servername,'Round') == 'Tab2') {
    $message = "Jogador 2 coloque 3 barcos";

    if ($seuplayer == 2) {
        if ($pi !== null && isset($TABULEIRO[$pi][$pj]) && $TABULEIRO[$pi][$pj] === '-') { // <- correção
            $TABULEIRO[$pi][$pj] = 'B';
            write_server($servername, 'Tab2', serialize($TABULEIRO));
        }

        if (count_boats($TABULEIRO) >= 3) {
            write_server($servername,'Round','ROUND1');
            write_server($servername,'LRound','ROUND1');
        }
    } else $block = true;
}

// ---------------------------
// DESATIVAÇÃO DE BOTÕES (APOS FASE DE BARCOS)
// ---------------------------
$tdisabled = [];
$current_round = read($servername,'Round');

if ($current_round != 'Tab1' && $current_round != 'Tab2') {
    foreach ($TABULEIRO as $r => $linha) {
        foreach ($linha as $c => $val) {
            if ($val == 'X' || $val == 'O') { // B removido para poder atacar barco inimigo
                $tdisabled[] = [$r,$c];
            }
        }
    }
}

// ---------------------------
// ROUND 1
// ---------------------------
if ($current_round == 'ROUND1') {
    $message = "Jogador 1 ataque uma posição";

    $PTAB = unserialize(read($servername,'Tab2'));
    if (!is_array($PTAB)) $PTAB = array_fill(0,6,array_fill(0,6,'-'));

    $raw = read($servername,'TabR'.$seuplayer);
    $TABULEIRO = $raw ? unserialize($raw) : array_fill(0,6,array_fill(0,6,'-'));

    if ($seuplayer == 1) {
        if ($pi !== null && isset($TABULEIRO[$pi][$pj])) {
            $TABULEIRO[$pi][$pj] = 'X';

            if ($PTAB[$pi][$pj] == 'B') {
                $PTAB[$pi][$pj] = 'O';
                $TABULEIRO[$pi][$pj] = 'O';
                write_server($servername,'Tab2',serialize($PTAB));
            }

            write_server($servername,'TabR1',serialize($TABULEIRO));
            write_server($servername,'Round','ROUND2');
            write_server($servername,'LRound','ROUND2');
        }
    } else $block = true;
}

// ---------------------------
// ROUND 2
// ---------------------------
if ($current_round == 'ROUND2') {
    $message = "Jogador 2 ataque uma posição";

    $PTAB = unserialize(read($servername,'Tab1'));
    if (!is_array($PTAB)) $PTAB = array_fill(0,6,array_fill(0,6,'-'));

    $raw = read($servername,'TabR'.$seuplayer);
    $TABULEIRO = $raw ? unserialize($raw) : array_fill(0,6,array_fill(0,6,'-'));

    if ($seuplayer == 2) {
        if ($pi !== null && isset($TABULEIRO[$pi][$pj])) {
            $TABULEIRO[$pi][$pj] = 'X';

            if ($PTAB[$pi][$pj] == 'B') {
                $PTAB[$pi][$pj] = 'O';
                $TABULEIRO[$pi][$pj] = 'O';
                write_server($servername,'Tab1',serialize($PTAB));
            }

            write_server($servername,'TabR2',serialize($TABULEIRO));
            write_server($servername,'Round','ROUND1');
            write_server($servername,'LRound','ROUND1');
        }
    } else $block = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Game - BatalhaNavalPHP</title>
</head>
<body>

<h2>Jogo iniciado!</h2>
<h3>Recarregue a página para atualizações</h3>

<?php 
echo $pmessage."<br>";
echo $message."<br><br>";
?>

<form method="post">
<?php
echo "X__0_1_2__3_4_5<br>";

for ($row = 0; $row < 6; $row++) {
    echo $row."| ";
    for ($col = 0; $col < 6; $col++) {

        $btn_disabled = $block ? 'disabled' : '';

        foreach ($tdisabled as $d) {
            if ($d[0] == $row && $d[1] == $col) {
                $btn_disabled = 'disabled'; break;
            }
        }

        $val = $TABULEIRO[$row][$col] ?? '-';

        echo '<button type="submit" name="pos" value="'.$row.'-'.$col.'" '.$btn_disabled.'>'.$val.'</button> ';
    }
    echo "<br>";
}
?>
<br><button>Recarregar</button>
</form>

<script>
window.addEventListener("beforeunload", () => {
    navigator.sendBeacon("leave.php?id=<?= $id ?>&player=<?= $seuplayer ?>&liberate=<?= $liberate ?>");
});
</script>

</body>
</html>
