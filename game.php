<?php
//quando criei esse codigo so eu e deus sabia ler
//agora e so deus boa sorte


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

//inicio da logica comum
$seuplayer = null;

if (read($servername, 'Player1') == $_SESSION['usuario']) {
    $seuplayer = 1;
    
} else if (read($servername,'Player2') == $_SESSION['usuario']) {
    $seuplayer = 2;
    
} else {
    die('Player inesistente');
}

//logica do leave
$liberate = 'false';

if($seuplayer == 1) {
    if(read($servername,'Round') == '1L') {
        write_server($servername, 'Round', read($servername, 'LRound'));
        $liberate = 'true';
    }
}
if($seuplayer == 2) {
    if(read($servername,'Round') == '2L') {
        write_server($servername, 'Round', read($servername, 'LRound'));
        $liberate = 'true';
    }
}
//denovo para verificar se deu erro
if (read($servername, 'Round') == '1L' or read($servername, 'Round') == '2L') {
    die('dead server error :(');
}

$pmessage = '';
$message = '';

//inicio da logica do game

$disabled = '';
$block = false;
//para nao quebrar o jogo
$tdisabled[0] = [0,0];

if (read($servername, 'Round') === 'START') {
    $pmessage = 'ESPERE O PLAYER 2';
}

if($seuplayer == 1) {
    $pmessage = 'VOCE E O PLAYER 1';
}
if($seuplayer == 2) {
    $pmessage = 'VOCE E O PLAYER 2';
}

$TABULEIRO = make_board();

write_server($servername, 'Tab' . $seuplayer, serialize($TABULEIRO));

if(read($servername,'Round') == 'Tab1') {
    $message = "Jogador 1 escolha seus barcos";
    if($seuplayer == 1) {
        
    }
    if($seuplayer == 2) {
        $block = true;
    }
}
$tdisabled[1] = [3,3];




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
echo $pmessage;
echo '<br>';
echo $message;
?>
<br>
<br>
<div class="tab">
    <?php
        if($block) {
            $disabled = 'disabled';
        }
        echo 'X__1_2_3__4_5_6';
        echo '<br>';
        //cria todas as linhas
        for ( $i = 1; $i < 7; $i++) {
            echo $i .'| ';
            //cria as colunas
            for ($j = 1; $j < 7; $j++) {
                //verifica botao desativado
                foreach ($tdisabled as $k) {
                    if ($k[0] == $i) {
                        if ($k[1] == $j) {
                            $disabled = 'disabled';
                        }
                    }
                    
                }


                echo '<button ' . $disabled . '>-</button>';
                echo '  ';
                if (!$block) {
                    $disabled = '';
                }
            }
            echo '<br>';
        }
    ?>
</div>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(`leave.php?id=<?= $id ?>&player=<?= $seuplayer ?>&liberate=<?= $liberate ?>`);
    });
</script>
</html>
