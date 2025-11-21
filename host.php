<?php 
session_start();

//cria a pasta de servidores, se não existir
$dirname = "servidor/";
if (!is_dir($dirname)) {
    mkdir($dirname, 0777, true);
}

//encontra um nome de servidor disponivel
$id = 1;
$servername = $dirname . "servidor_" . $id . ".php";
while (file_exists($servername)) {
    $id += 1;
    $servername = $dirname . "servidor_" . $id . ".php";
}

//salva o nome do servidor na sessao
if (!isset($_SESSION['serverc'])) {
   $_SESSION['serverc'] = $servername;
} else {
    $servername = $_SESSION['serverc'];
}

//cria o arquivo do servidor
$fp = fopen($servername,'w');

//copia a base do servidor
fwrite($fp, file_get_contents('serverbase.php'));
fclose($fp);

require $servername;

//funcao para escrever no arquivo do servidor
function writeserver($var, $value) {
    global $servername;

    // Carrega o arquivo
    require $servername;
    
    // Altera o valor
    $serverinfo[$var] = $value;

    // Regera o PHP
    $template = "<?php\n\$serverinfo = " . var_export($serverinfo, true) . ";\n";

    // Escreve o arquivo
    file_put_contents($servername, $template);
}


//coloca o jogador 1 como o usuario

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    writeserver("Player1", $_SESSION['usuario']);
    require $servername;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Host - BatalhaNavalPHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1>Você está hospedando um jogo!</h1>
    <h3>Jogadores:</h3>
    <h4><?php echo $serverinfo['Player1']; ?></h4>
    <h4><?php echo $serverinfo['Player2'] ?? "Aguardando jogador 2..."; ?></h4>
    <form method="post">
        <button type="submit" name="startgame">Iniciar Jogo</button>
    </form>
</body>
<script>
    window.addEventListener("beforeunload", function () {
        navigator.sendBeacon("delete.php");
    });
</script>
</html>