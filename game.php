<?php
//quando criei esse codigo so eu e deus sabia mecher
//agora e so deus boa sorte

session_start();
require 'basescripts.php';

// cria pasta
$dirname = "servidor/";
if (!is_dir($dirname)) mkdir($dirname, 0777, true);

// servidor alvo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$servername = $dirname . "servidor_" . $id . "/";
if (!is_dir($servername)) die("Jogo não encontrado.");

// identifica o player
$seuplayer = null;
if (read($servername,'Player1') == $_SESSION['usuario']) $seuplayer = 1;
else if (read($servername,'Player2') == $_SESSION['usuario']) $seuplayer = 2;
else die('Player inexistente');

// lógica leave
$liberate = 'false';
if ($seuplayer == 1 && read($servername,'Round') == '1L'){ write_server($servername,'Round',read($servername,'LRound')); $liberate='true'; }
if ($seuplayer == 2 && read($servername,'Round') == '2L'){ write_server($servername,'Round',read($servername,'LRound')); $liberate='true'; }
if (read($servername,'Round')=='1L'||read($servername,'Round')=='2L') die('dead server error :(');

$pmessage = '';
$message = "";
$block = false;

// leitura segura do tabuleiro
$raw = read($servername,'Tab'.$seuplayer);
$TABULEIRO = $raw ? @unserialize($raw) : null;
if (!is_array($TABULEIRO)) $TABULEIRO = array_fill(0,6,array_fill(0,6,'-'));

// POST
$pi = $pj = null;
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['pos'])) list($pi,$pj)=array_map('intval',explode('-',$_POST['pos']));

// conta barcos
function count_boats(array $t){
    $c=0; foreach($t as $r) foreach($r as $v) if($v==='B')$c++; return $c;
}

// >>> FUNÇÃO DE VITÓRIA <<<
function check_win($servername){
    $t1 = unserialize(read($servername,'Tab1')); if (!is_array($t1)) return null;
    $t2 = unserialize(read($servername,'Tab2')); if (!is_array($t2)) return null;

    if(count_boats($t1)==0) return 2; // Player 2 venceu
    if(count_boats($t2)==0) return 1; // Player 1 venceu
    return null;
}

$pmessage="VOCÊ É O PLAYER $seuplayer";
$current_round = read($servername,'Round');

// ================= TELA DE VITÓRIA =================
if($current_round=='WIN1'){
    echo "<h1>PLAYER 1 VENCEU!</h1>";
    die();
}
if($current_round=='WIN2'){
    echo "<h1>PLAYER 2 VENCEU!</h1>";
    die();
}

// ================= TAB1 =================
if($current_round=='Tab1'){
    $message="Jogador 1 coloque 3 barcos";

    if($seuplayer==1){
        if($pi!==null && $TABULEIRO[$pi][$pj]=='-'){
            $TABULEIRO[$pi][$pj]='B';
            write_server($servername,'Tab1',serialize($TABULEIRO));
        }
        if(count_boats($TABULEIRO)>=5){ write_server($servername,'Round','Tab2'); write_server($servername,'LRound','Tab2'); }
    }else $block=true;
}

// ================= TAB2 =================
if($current_round=='Tab2'){
    $message="Jogador 2 coloque 3 barcos";

    if($seuplayer==2){
        if($pi!==null && $TABULEIRO[$pi][$pj]=='-'){
            $TABULEIRO[$pi][$pj]='B';
            write_server($servername,'Tab2',serialize($TABULEIRO));
        }
        if(count_boats($TABULEIRO)>=5){ write_server($servername,'Round','ROUND1'); write_server($servername,'LRound','ROUND1'); }
    }else $block=true;
}

// desativar botões depois da fase de barcos
// Desabilitar apenas as jogadas já feitas pelo jogador atual
if($current_round!='Tab1' && $current_round!='Tab2'){
    $raw = read($servername,'TabR'.$seuplayer);
    $TABR = $raw ? unserialize($raw) : array_fill(0,6,array_fill(0,6,'-'));

    foreach($TABR as $r=>$ln) foreach($ln as $c=>$v){
        if($v=='X' || $v=='O') $tdisabled[]=[$r,$c]; // só o que ele já tentou
    }
}

// ================= ROUND1 =================
if($current_round=='ROUND1'){
    $message="Jogador 1 ataque";

    $PTAB = unserialize(read($servername,'Tab2')); if(!is_array($PTAB))$PTAB=array_fill(0,6,array_fill(0,6,'-'));
    $raw=read($servername,'TabR'.$seuplayer);
    $TABULEIRO=$raw?unserialize($raw):array_fill(0,6,array_fill(0,6,'-'));

    if($seuplayer==1){
        if($pi!==null){
            $TABULEIRO[$pi][$pj]='X';

            if($PTAB[$pi][$pj]=='B'){
                $TABULEIRO[$pi][$pj]='O';
                $PTAB[$pi][$pj]='O';
                write_server($servername,'Tab2',serialize($PTAB));
            }

            write_server($servername,'TabR1',serialize($TABULEIRO));

            // >>> CHECA VITÓRIA <<<
            $v = check_win($servername);
            if($v){ write_server($servername,'Round',"WIN$v"); }

            if(!$v){
                write_server($servername,'Round','ROUND2');
                write_server($servername,'LRound','ROUND2');
            }
        }
    }else $block=true;
}

// ================= ROUND2 =================
if($current_round=='ROUND2'){
    $message="Jogador 2 ataque";

    $PTAB = unserialize(read($servername,'Tab1')); if(!is_array($PTAB))$PTAB=array_fill(0,6,array_fill(0,6,'-'));
    $raw=read($servername,'TabR'.$seuplayer);
    $TABULEIRO=$raw?unserialize($raw):array_fill(0,6,array_fill(0,6,'-'));

    if($seuplayer==2){
        if($pi!==null){
            $TABULEIRO[$pi][$pj]='X';

            if($PTAB[$pi][$pj]=='B'){
                $TABULEIRO[$pi][$pj]='O';
                $PTAB[$pi][$pj]='O';
                write_server($servername,'Tab1',serialize($PTAB));
            }
            write_server($servername,'TabR2',serialize($TABULEIRO));

            // >>> CHECA VITÓRIA <<<
            $v = check_win($servername);
            if($v){ write_server($servername,'Round',"WIN$v"); }

            if(!$v){
                write_server($servername,'Round','ROUND1');
                write_server($servername,'LRound','ROUND1');
            }
        }
    }else $block=true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><title>Batalha Naval</title></head>
<body>
<h2>Jogo iniciado!</h2>
<h3>Recarregue a página para ver atualizações</h3>

<?php echo $pmessage."<br>".$message."<br><br>"; ?>

<form method="post">
<?php
echo "X__0_1_2__3_4_5<br>";
for($r=0;$r<6;$r++){
    echo "$r| ";
    for($c=0;$c<6;$c++){
        $d=$block?'disabled':'';
        foreach($tdisabled as $x) if($x[0]==$r&&$x[1]==$c)$d='disabled';
        $v=$TABULEIRO[$r][$c]??'-';
        echo "<button type='submit' name='pos' value='$r-$c' $d>$v</button> ";
    }
    echo "<br>";
}
?>
<br><button>Recarregar</button>
</form>

<script>
window.addEventListener("beforeunload",()=> {
    navigator.sendBeacon("leave.php?id=<?= $id ?>&player=<?= $seuplayer ?>&liberate=<?= $liberate ?>");
});
</script>
</body>
</html>
