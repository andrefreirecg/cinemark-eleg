<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

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

    $cabecalhos_obrigatorios = ['COD_PIPOCA', 'COD_REFRIGERANTE'];

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
            $post_id = criar_post_voucher_ingresso($codigo, $index + 1);
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

function criar_post_voucher_ingresso($codigo, $index)
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
    return $post_id;
}

function criar_combos($dados_agrupados)
{
    $posts_criados = [];
    $combos_ja_cadastrados = [];
    if ($dados_agrupados['COD_PIPOCA']) {
        foreach ($dados_agrupados['COD_PIPOCA'] as $index => $codigo) {
            $refrigerante = $dados_agrupados['COD_REFRIGERANTE'][$index];

            $post_id = criar_post_combo($codigo, $refrigerante, $index + 1);
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

function criar_post_combo($pipoca, $refrigerante, $index)
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
    return $post_id;
}


function update_estoque_vouchers($post_type)
{
    $vouchers_disponiveis_args = [
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'pedido',
                'value' => '',
                'compare' => '='
            ],
            [
                'key' => 'pedido',
                'compare' => 'NOT EXISTS'
            ]
        ]

    ];
    $vouchers_disponiveis_query = new WP_Query($vouchers_disponiveis_args);
    $vouchers_disponiveis = $vouchers_disponiveis_query->get_posts();
    $quantidade_vouchers_disponiveis = count($vouchers_disponiveis);
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
            $estoque = $quantidade_vouchers_disponiveis;
            update_post_meta($produto->ID, '_stock', $estoque);
        }
    }
}
