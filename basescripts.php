<?php

function writeserver($var, $value) {
    global $servername;

    // Carrega os dados existentes
    if (file_exists($servername)) {
        require $servername;
    } else {
        $serverinfo = [];
    }

    // Altera
    $serverinfo[$var] = $value;

    // Salva somente os dados, sem lógica
    $template = "<?php\n\$serverinfo = " . var_export($serverinfo, true) . ";\n";
    file_put_contents($servername, $template);
}

function pushserver() {
    // Mantido só para não dar erro caso seja chamado
    return;
}
