<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;


function importar_planilha_cinemark()
{
    // Verifica se tem um arquivo
    if (!isset($_FILES['planilha'])) {
        wp_send_json([
            'mensagem' => 'Nenhuma planilha enviada.',
        ]);
    }
    $arquivo = $_FILES['planilha'];
    $caminho_final = armazenar_planilha($arquivo);

    try_parse_planilha($caminho_final);
}

add_action('wp_ajax_importar_planilha_cinemark', 'importar_planilha_cinemark');

function validar_senha_usuario_internamente($senha)
{
    // Sanitiza a senha recebida
    $senha = sanitize_text_field($senha);


    // Garante que o usuário esteja logado
    if (!is_user_logged_in() || empty($senha)) {
        return [
            'status' => false,
            'mensagem' => 'Usuário não autenticado.'
        ];
    }

    // Pega o usuário logado
    $user = wp_get_current_user();

    // Verifica a senha
    if (!$user || !wp_check_password($senha, $user->user_pass, $user->ID)) {
        return [
            'status' => false,
            'mensagem' => 'Senha incorreta.'
        ];
    }

    // Tudo certo
    return [
        'status' => true,
        'mensagem' => 'Senha validada com sucesso.'
    ];
}

function try_parse_planilha($caminho_final)
{
    try {
        $dados_agrupados = parse_planilha($caminho_final);

        wp_send_json_success([
            'mensagem' => 'Planilha importada com sucesso!',
            'dados' => $dados_agrupados,
            'planilha' => $caminho_final
        ]);
    } catch (Exception $e) {
        wp_send_json_error([
            'mensagem' => 'Erro ao ler a planilha: ' . $e->getMessage(),
        ]);
    }
}

function parse_planilha($caminho_final)
{
    $spreadsheet = IOFactory::load($caminho_final);
    $sheet = $spreadsheet->getActiveSheet();

    $dados_agrupados = [];
    $cabecalhos = [];
    $linha_index = 0;

    $cabecalhos_obrigatorios = ['COD_PIPOCA', 'COD_REFRIGERANTE', 'VALIDADE'];

    foreach ($sheet->getRowIterator() as $linha) {
        $cellIterator = $linha->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $valores = [];

        foreach ($cellIterator as $celula) {
            $valores[] = trim((string) $celula->getValue());
        }

        if ($linha_index === 0) {
            $cabecalhos = $valores;

            $faltando = array_diff($cabecalhos_obrigatorios, $cabecalhos);
            if (!empty($faltando)) {
                throw new Error('Faltam os seguintes cabecalhos: ' . implode(', ', $faltando));
            }

            foreach ($cabecalhos as $cabecalho) {
                $dados_agrupados[$cabecalho] = [];
            }
        } else {
            foreach ($valores as $i => $valor) {
                $coluna = $cabecalhos[$i] ?? "Coluna_$i";
                $dados_agrupados[$coluna][] = $valor;
            }
        }

        $linha_index++;
    }

    return remover_valores_em_branco($dados_agrupados);
}

function remover_valores_em_branco($dados_agrupados)
{
    foreach ($dados_agrupados as $coluna => &$valores) {
        // Encontra o último índice com valor não vazio
        $ultimo_valor_index = -1;
        foreach ($valores as $i => $val) {
            if (trim($val) !== '') {
                $ultimo_valor_index = $i;
            }
        }

        if ($ultimo_valor_index === -1) {
            unset($dados_agrupados[$coluna]);
            continue;
        }

        $valores = array_slice($valores, 0, $ultimo_valor_index + 1);
    }
    unset($valores);
    return $dados_agrupados;
}


function confirmar_importacao()
{

    $result = validar_senha_usuario_internamente($_POST['password']);
    if (!$result['status']) {
        wp_send_json_error([
            'mensagem' => $result['mensagem'],
        ]);
    }

    $dados_agrupados = parse_planilha($_POST['url_planilha']);
    $ingressos = criar_vouchers_ingressos($dados_agrupados);
    $combos = criar_combos($dados_agrupados);

    wp_send_json_success([
        'mensagem' => 'Importação finalizada com sucesso!',
        'ingressos_total' => count($ingressos['novos']),
        'ingressos_ja_cadastrados' => count($ingressos['ja_cadastrados']),
        'ingressos_cadastrados' => $ingressos['ja_cadastrados'],
        'combos_total' => count($combos),
        'combos_ja_cadastrados' => count($combos['ja_cadastrados']),
        'combos_cadastrados' => $combos['ja_cadastrados'],
    ]);
    exit();
}

add_action('wp_ajax_confirmar_importacao', 'confirmar_importacao');


function criar_vouchers_ingressos($dados_agrupados)
{
    $posts_criados = [];
    $ingresso_ja_cadastrados = [];
    if ($dados_agrupados['COD_INGRESSO']) {
        foreach ($dados_agrupados['COD_INGRESSO'] as $index => $codigo) {
            $dateTime = Date::excelToDateTimeObject($dados_agrupados['VALIDADE'][$index]);
            $validade = $dateTime->format('d/m/Y');
            $post_id = criar_post_voucher_ingresso($codigo, $index + 1, $validade);
            if ($post_id) {
                $posts_criados[] = $post_id;
            } else {
                $ingresso_ja_cadastrados[] = $codigo;
            }
        }
    }

    update_estoque_vouchers('vouchers_ingresso');

    return [
        'novos' => $posts_criados,
        'ja_cadastrados' => $ingresso_ja_cadastrados
    ];
}

function criar_post_voucher_ingresso($codigo, $index, $validade)
{
    $post_exist_args = [
        'post_type' => 'vouchers_ingresso',
        'meta_query' => [
            [
                'key' => 'ingresso',
                'value' => $codigo,
                'compare' => '='
            ]
        ]
    ];
    $post_exist_query = new WP_Query($post_exist_args);
    if ($post_exist_query->have_posts()) {
        return false;
    }

    $post_title = ' Ingresso | Importação dia: ' . date('d/m/Y') . ' - ' . $index;
    $post = [
        'post_type' => 'vouchers_ingresso',
        'post_title' => $post_title,
        'post_status' => 'publish'
    ];
    $post_id = wp_insert_post($post);
    carbon_set_post_meta($post_id, 'ingresso', $codigo);
    carbon_set_post_meta($post_id, 'validade', $validade);
    return $post_id;
}

function criar_combos($dados_agrupados)
{
    $posts_criados = [];
    $combos_ja_cadastrados = [];
    if ($dados_agrupados['COD_PIPOCA']) {
        foreach ($dados_agrupados['COD_PIPOCA'] as $index => $codigo) {
            $refrigerante = $dados_agrupados['COD_REFRIGERANTE'][$index];
            $dateTime = Date::excelToDateTimeObject($dados_agrupados['VALIDADE'][$index]);
            $validade = $dateTime->format('d/m/Y');
            $post_id = criar_post_combo($codigo, $refrigerante, $index + 1, $validade);
            if ($post_id) {
                $posts_criados[] = $post_id;
            } else {
                $combos_ja_cadastrados[] = $codigo;
            }
        }
    }
    update_estoque_vouchers('vouchers_combo');

    return [
        'novos' => $posts_criados,
        'ja_cadastrados' => $combos_ja_cadastrados
    ];
}

function criar_post_combo($pipoca, $refrigerante, $index, $validade)
{

    $post_exist_args_pipoca = [
        'post_type' => 'vouchers_combo',
        'meta_query' => [
            [
                'key' => 'pipoca',
                'value' => $pipoca,
                'compare' => '='
            ]
        ]
    ];

    $post_exist_args_refrigerante = [
        'post_type' => 'vouchers_combo',
        'meta_query' => [
            [
                'key' => 'refrigerante',
                'value' => $refrigerante,
                'compare' => '='
            ]
        ]
    ];

    $post_exist_query_pipoca = new WP_Query($post_exist_args_pipoca);
    $post_exist_query_refrigerante = new WP_Query($post_exist_args_refrigerante);

    if ($post_exist_query_pipoca->have_posts() || $post_exist_query_refrigerante->have_posts()) {
        return false;
    }

    $post_title = 'Combo | Importação dia: ' . date('d/m/Y') . ' - ' . $index;
    $post = [
        'post_type' => 'vouchers_combo',
        'post_title' => $post_title,
        'post_status' => 'publish'
    ];
    $post_id = wp_insert_post($post);
    carbon_set_post_meta($post_id, 'pipoca', $pipoca);
    carbon_set_post_meta($post_id, 'refrigerante', $refrigerante);
    carbon_set_post_meta($post_id, 'validade', $validade);
    return $post_id;
}

add_action('wp_ajax_update_estoque_vouchers_ingresso', 'update_estoque_vouchers_ingresso');

function update_estoque_vouchers_ingresso(){
    $qtd_validos = update_estoque_vouchers('vouchers_ingresso');
    wp_send_json_success(['mensagem' => 'Estoque de ingressos atualizado com sucesso.', 'quantidade'=> $qtd_validos]);
    exit();
}

add_action('wp_ajax_update_estoque_vouchers_combos', 'update_estoque_vouchers_combos');

function update_estoque_vouchers_combos(){
    $qtd_validos = update_estoque_vouchers('vouchers_combo');
    wp_send_json_success(['mensagem' => 'Estoque de comboss atualizado com sucesso.', 'quantidade'=> $qtd_validos]);
    exit();
}


function update_estoque_vouchers($post_type)
{
    $vouchers_disponiveis_args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
    ];
    $todos_vouchers = new WP_Query($vouchers_disponiveis_args);
    $vouchers_validos = 0;

    foreach ($todos_vouchers->get_posts() as $voucher) {
        $pedido = carbon_get_post_meta($voucher->ID, 'pedido');

        $order_exist = wc_get_order($pedido);

        if ($order_exist) {
            continue;
        }

        $validade = carbon_get_post_meta($voucher->ID, 'validade');

        $partes = explode('/', $validade);

        if (count($partes) != 3) {
            continue;
        }

        $validade_formatada = $partes[2] . '-' . $partes[1] . '-' . $partes[0];

        $validade_date = date_create($validade_formatada);

        $hoje = new DateTime('today');
        if ($validade_date < $hoje) {
            continue;
        }
        $vouchers_validos++;
    }

    $produtos_vinculados_args = [
        'post_type' => 'product',
        'meta_query' => [
            [
                'key' => 'post_type',
                'value' => $post_type,
                'compare' => 'IN'
            ]
        ]
    ];
    $produtos_vinculados_query = new WP_Query($produtos_vinculados_args);
    $produtos_vinculados = $produtos_vinculados_query->get_posts();

    if (!empty($produtos_vinculados)) {
        foreach ($produtos_vinculados as $produto) {
            update_post_meta($produto->ID, '_stock', $vouchers_validos);
        }
    }
    return $vouchers_validos;
}
