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
$tdisabled[0] = [10,10];

if (read($servername, 'Round') === 'START') {
    $pmessage = 'ESPERE O PLAYER 2';
}

if($seuplayer == 1) {
    $pmessage = 'VOCE E O PLAYER 1';
}
if($seuplayer == 2) {
    $pmessage = 'VOCE E O PLAYER 2';
}

$i = 10;
$j = 10;
$TABULEIRO = unserialize(read($servername, 'Tab' . $seuplayer));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['pos'])) {
        list($i, $j) = explode('-', $_POST['pos']);
    }
}

$boatsplaced = 0;
//vez do player 1
if(read($servername,'Round') == 'Tab1') {
    $boatsplaced = 0;
    $message = "Jogador 1 escolha seus barcos \n clique aonde deseja colocar o barco";
    if($seuplayer == 1) {
        $TABULEIRO[$i][$j] = 'B'; 
        write_server($servername, 'Tab1', serialize($TABULEIRO));
        foreach($TABULEIRO as $key => $value) {
            foreach($value as $k => $v) {
                if($v == 'B') {
                    $boatsplaced += 1;
                }
            }
        }
        if($boatsplaced >= 3) {
            unset($_POST['pos']);
            write_server($servername, 'Round', 'Tab2');
            write_server($servername, 'LRound', 'Tab2');
        }
    }
    if($seuplayer == 2) {
        $block = true;
    }
}

//vez do player 2
if(read($servername,'Round') == 'Tab2') {
    $boatsplaced = 0;
    unset($_POST['pos']);
    $message = "Jogador 2 escolha seus barcos \n clique aonde deseja colocar o barco";
    if($seuplayer == 1) {
        $block = true;
    }
    if($seuplayer == 2) {
        $TABULEIRO[$i][$j] = 'B'; 
        write_server($servername, 'Tab2', serialize($TABULEIRO));
        foreach($TABULEIRO as $key => $value) {
            foreach($value as $k => $v) {
                if($v == 'B') {
                    $boatsplaced += 1;
                }
            }
        }
        if($boatsplaced >= 3) {
            unset($_POST['pos']);
            write_server($servername, 'Round', 'ROUND1');
            write_server($servername, 'LRound', 'ROUND1');
        }
    }
}

foreach($TABULEIRO as $key => $value) {
    foreach($value as $k => $v) {
        if($v == 'X' or $v == 'O' or $v == 'B') {
            $tdisabled[] = [$key, $k];
        }
    }
}

var_dump($boatsplaced);

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
<form method="post">
    <?php
        if($block) {
            $disabled = 'disabled';
        }
        echo 'X__0_1_2__3_4_5';
        echo '<br>';
        //cria todas as linhas++
        for ( $i = 0; $i < 6; $i++) {
            echo $i .'| ';
            //cria as colunas
            for ($j = 0; $j < 6; $j++) {
                //verifica botao desativado
                foreach ($tdisabled as $k) {
                    if ($k[0] == $i) {
                        if ($k[1] == $j) {
                            $disabled = 'disabled';
                        }
                    }
                    
                }


                echo '<button type="submit" name="pos" value="'.$i.'-'.$j.'" ' . $disabled . '>' . $TABULEIRO[$i][$j] . '</button>';
                echo '  ';
                if (!$block) {
                    $disabled = '';
                }
            }
            echo '<br>';
        }
    ?>
    <br>
    <button type="submit" name="refresh">Recarregar</button>
</form>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon(`leave.php?id=<?= $id ?>&player=<?= $seuplayer ?>&liberate=<?= $liberate ?>`);
    });
</script>
</html>
