<?php

function writeserver($var, $value, $servername) {
    if (!file_exists($servername)) {
        die("ERRO: Arquivo não encontrado");
    }

    // Carrega $serverinfo
    require $servername;

    if (!isset($serverinfo) || !is_array($serverinfo)) {
        die("ERRO: Arquivo não contém \$serverinfo válido");
    }

    // Altera
    $serverinfo[$var] = $value;

    // Salva
    $template = "<?php\n\$serverinfo = " . var_export($serverinfo, true) . ";\n";
    file_put_contents($servername, $template);

    return true;
}

function pushserver() {
    // Mantido só para não dar erro caso seja chamado
    return;
}
