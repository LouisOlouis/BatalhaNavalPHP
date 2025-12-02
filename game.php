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

// mensagens
$pmessage = '';
$message = "";

// flags
$block = false;

// leitura segura do tabuleiro do jogador
$raw = read($servername, 'Tab'.$seuplayer);
$TABULEIRO = $raw ? @unserialize($raw) : null;
if (!is_array($TABULEIRO)) {
    $TABULEIRO = array_fill(0, 6, array_fill(0, 6, '-'));
}

// leitura do POST sem sobrescrita pelos loops
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

// mensagem do player
$pmessage = "VOCÊ É O PLAYER ".$seuplayer;

// Variável para decidir qual tabuleiro será mostrado na tela
// Por padrão mostramos o tabuleiro do jogador (fase de colocação)
$displayBoard = $TABULEIRO;

// Zera lista de células desativadas — vamos construir com base no displayBoard
$tdisabled = [];

// ---------------------------
// FASE TAB1 (PLAYER 1 COLOCA BARCOS)
// ---------------------------
if (read($servername,'Round') == 'Tab1') {
    $message = "Jogador 1: coloque 3 barcos";
    // o bloqueio depende de quem é o jogador nessa fase
    $block = ($seuplayer != 1);

    if ($seuplayer == 1 && $pi !== null && isset($TABULEIRO[$pi][$pj]) && $TABULEIRO[$pi][$pj] !== 'B') {
        $TABULEIRO[$pi][$pj] = 'B';
        write_server($servername, 'Tab1', serialize($TABULEIRO));
    }

    if (count_boats($TABULEIRO) >= 3) {
        write_server($servername, 'Round', 'Tab2');
        write_server($servername, 'LRound', 'Tab2');
    }

    // mostrará o tabuleiro do jogador (onde coloca barcos)
    $displayBoard = $TABULEIRO;
}

// ---------------------------
// FASE TAB2 (PLAYER 2 COLOCA BARCOS)
// ---------------------------
if (read($servername,'Round') == 'Tab2') {
    $message = "Jogador 2: coloque 3 barcos";
    $block = ($seuplayer != 2);

    if ($seuplayer == 2 && $pi !== null && isset($TABULEIRO[$pi][$pj]) && $TABULEIRO[$pi][$pj] !== 'B') {
        $TABULEIRO[$pi][$pj] = 'B';
        write_server($servername, 'Tab2', serialize($TABULEIRO));
    }

    if (count_boats($TABULEIRO) >= 3) {
        write_server($servername, 'Round', 'ROUND1');
        write_server($servername, 'LRound', 'ROUND1');
    }

    $displayBoard = $TABULEIRO;
}

// ---------------------------
// ROUND 1 (PLAYER 1 ATACA)
// ---------------------------
if (read($servername,'Round') == 'ROUND1') {
    $message = "ROUND1: Jogador 1 escolha onde atacar";
    // desbloqueia apenas para player 1
    $block = ($seuplayer != 1);

    // PTAB = tabuleiro do oponente (player 2)
    $PTAB = @unserialize(read($servername,'Tab2'));
    if (!is_array($PTAB)) $PTAB = array_fill(0,6,array_fill(0,6,'-'));

    // tabuleiro de ataque do seuplayer
    $rawR = read($servername, 'TabR'.$seuplayer);
    $TABR = $rawR ? @unserialize($rawR) : array_fill(0,6,array_fill(0,6,'-'));

    if ($seuplayer == 1 && $pi !== null && isset($TABR[$pi][$pj])) {
        // marca tentativa
        if ($TABR[$pi][$pj] !== 'X' && $TABR[$pi][$pj] !== 'O') {
            $TABR[$pi][$pj] = 'X';
            if ($PTAB[$pi][$pj] == 'B') {
                $PTAB[$pi][$pj] = 'O';
                $TABR[$pi][$pj] = 'O';
                write_server($servername,'Tab2', serialize($PTAB));
            }
            write_server($servername,'TabR1', serialize($TABR));
            write_server($servername,'Round','ROUND2');
            write_server($servername,'LRound','ROUND2');
        }
    }

    // O tabuleiro exibido para o jogador é seu TabR (tabuleiro de ataque)
    $displayBoard = $TABR;

    // desativa posições X/O já usadas (no displayBoard)
    foreach ($displayBoard as $r => $linha) {
        foreach ($linha as $c => $val) {
            if ($val == 'X' || $val == 'O') {
                $tdisabled[] = [$r, $c];
            }
        }
    }
}

// ---------------------------
// ROUND 2 (PLAYER 2 ATACA)
// ---------------------------
if (read($servername,'Round') == 'ROUND2') {
    $message = "ROUND2: Jogador 2 escolha onde atacar";
    $block = ($seuplayer != 2);

    $PTAB = @unserialize(read($servername,'Tab1'));
    if (!is_array($PTAB)) $PTAB = array_fill(0,6,array_fill(0,6,'-'));

    $rawR = read($servername, 'TabR'.$seuplayer);
    $TABR = $rawR ? @unserialize($rawR) : array_fill(0,6,array_fill(0,6,'-'));

    if ($seuplayer == 2 && $pi !== null && isset($TABR[$pi][$pj])) {
        if ($TABR[$pi][$pj] !== 'X' && $TABR[$pi][$pj] !== 'O') {
            $TABR[$pi][$pj] = 'X';
            if ($PTAB[$pi][$pj] == 'B') {
                $PTAB[$pi][$pj] = 'O';
                $TABR[$pi][$pj] = 'O';
                write_server($servername,'Tab1', serialize($PTAB));
            }
            write_server($servername,'TabR2', serialize($TABR));
            write_server($servername,'Round','ROUND1');
            write_server($servername,'LRound','ROUND1');
        }
    }

    $displayBoard = $TABR;

    foreach ($displayBoard as $r => $linha) {
        foreach ($linha as $c => $val) {
            if ($val == 'X' || $val == 'O') {
                $tdisabled[] = [$r, $c];
            }
        }
    }
}

// ---------------------------
// Se estivermos em fase de colocação (Tab1/Tab2) precisamos também desativar células B no displayBoard
// (quando displayBoard é o próprio Tab do jogador)
if (read($servername,'Round') == 'Tab1' || read($servername,'Round') == 'Tab2') {
    foreach ($displayBoard as $r => $linha) {
        foreach ($linha as $c => $val) {
            if ($val == 'B') {
                $tdisabled[] = [$r, $c];
            }
        }
    }
}

// ---------------------------
// HTML da página
// ---------------------------
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
echo $pmessage . "<br>";
echo $message . "<br><br>";
?>

<form method="post">
<?php
echo "X__0_1_2__3_4_5<br>";

for ($row = 0; $row < 6; $row++) {
    echo $row . "| ";
    for ($col = 0; $col < 6; $col++) {

        $btn_disabled = ($block ? 'disabled' : '');

        foreach ($tdisabled as $d) {
            if ($d[0] == $row && $d[1] == $col) {
                $btn_disabled = "disabled";
                break;
            }
        }

        $val = isset($displayBoard[$row][$col]) ? $displayBoard[$row][$col] : '-';

        echo '<button type="submit" name="pos" value="'.$row.'-'.$col.'" '.$btn_disabled.'>'.$val.'</button> ';
    }
    echo "<br>";
}
?>
<br>
<button type="submit" name="refresh">Recarregar</button>
</form>

<script>
window.addEventListener("beforeunload", function () {
    navigator.sendBeacon(`leave.php?id=<?= $id ?>&player=<?= $seuplayer ?>&liberate=<?= $liberate ?>`);
});
</script>

</body>
</html>