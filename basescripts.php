<?php
function copiar_diretorio($origem, $destino) {
    // Abre o diretório de origem
    $dir = opendir($origem);
    
    // Cria o diretório de destino se ele não existir
    @mkdir($destino);
    
    // Loop através do conteúdo do diretório de origem
    while (false !== ($arquivo = readdir($dir))) {
        // Ignora os ponteiros de diretório padrão '.' e '..'
        if (($arquivo != '.') && ($arquivo != '..')) {
            $caminho_origem = $origem . '/' . $arquivo;
            $caminho_destino = $destino . '/' . $arquivo;
            
            // Se o item atual for um diretório, chama a função recursivamente
            if (is_dir($caminho_origem)) {
                copiar_diretorio($caminho_origem, $caminho_destino);
            } else {
                // Se for um arquivo, usa a função copy() para copiá-lo
                copy($caminho_origem, $caminho_destino);
            }
        }
    }
    
    // Fecha o diretório
    closedir($dir);
}

function delete_server($dir) {
    if (!is_dir($dir)) return;

    $itens = scandir($dir);

    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') continue;

        $caminho = $dir . '/' . $item;

        if (is_dir($caminho)) {
            delete_server($caminho);
        } else {
            unlink($caminho);
        }
    }

    rmdir($dir);

}

function write_server($dir, $var, $content) {
    if (!is_dir($dir)) return false;

    $caminho = $dir . $var. '.txt';

    return file_put_contents($caminho, $content) !== false;
}

function read($dir, $file) {

    $alvo = $dir . $file . '.txt';
    if (!is_file($alvo)) return 'NULL';

    return file_get_contents($alvo);
}

function make_board() {
    $linha = [];
    for ($i = 0; $i < 6; $i++) {
        $linha[] = '0';
    }

    $coluna = [];
    for ($i = 0; $i < 6; $i++) {
        $coluna[] = $linha;
    }
    
    return $coluna;
}
