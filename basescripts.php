<?php

function writeserver($var, $value) {
    global $servername;

    // Carrega o arquivo
    require $servername;
    
    // Altera o valor
    $serverinfo[$var] = $value;

    // Marca como alterado
    $serverinfo['Changes'] = true;

    // Regera o PHP
    $template = "<?php\n\$serverinfo = " . var_export($serverinfo, true) . ";\n";

    // Escreve o arquivo
    file_put_contents($servername, $template);

}
