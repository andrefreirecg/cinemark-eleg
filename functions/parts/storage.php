<?php


function armazenar_planilha($arquivo)
{
    $upload_dir = wp_upload_dir();
    $pasta_destino = $upload_dir['basedir'] . '/planilhas';

    // Cria a pasta se não existir
    if (!file_exists($pasta_destino)) {
        mkdir($pasta_destino, 0755, true);
    }

    $caminho_final = $pasta_destino . '/' . basename($arquivo['name']);

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_final)) {
        wp_send_json([
            'mensagem' => 'Erro ao mover o arquivo para o destino final.',
        ]);
    }
    return $caminho_final;
}
