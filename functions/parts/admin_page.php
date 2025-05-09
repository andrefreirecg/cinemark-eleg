<?php
// Hook para adicionar menu no admin
add_action('admin_menu', 'meu_plugin_adicionar_menu');

function meu_plugin_adicionar_menu()
{
    // Adiciona o menu principal
    add_menu_page(
        'Integração Cinemark - Elegibilidade',          // Título da página
        'IMPORTAÇÃO CINEMARK',          // Texto do menu
        'manage_options',      // Permissão necessária
        'cinemark-elegibilidade',          // Slug único
        'pagina_opcoes', // Callback para renderizar a página
        'dashicons-tickets-alt        ', // Ícone do menu (dashicons)
        25                     // Posição no menu
    );
}

// Função para renderizar o conteúdo da página
function pagina_opcoes()
{
?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <?php
        do_settings_sections('cinemark-elegibilidade');
        ?>
    </div>
<?php
}
// Hook para registrar configurações
add_action('admin_init', 'registrar_opcoes');

function registrar_opcoes()
{

    // Adiciona uma seção
    add_settings_section(
        'integracao_principal_cinemark',   // ID da seção
        'Configurações Principais',     // Título da seção
        'descricao_principal_callback',    // Callback da seção
        'cinemark-elegibilidade'                    // Slug da página
    );

    add_settings_field(
        'importar',               // ID do campo
        'Importar Vouchers',     // Título do campo
        'importar_callback',      // Callback para renderizar o botão
        'cinemark-elegibilidade',             // Slug da página
        'integracao_principal_cinemark'    // ID da seção
    );

    add_settings_field(
        'estoque_vouchers_ingresso',               // ID do campo
        'Sincronizar Quantidade',     // Título do campo
        'quantidade_ingresso_callback',      // Callback para renderizar o botão
        'cinemark-elegibilidade',             // Slug da página
        'integracao_principal_cinemark'    // ID da seção
    );
}

function quantidade_ingresso_callback(){
    ?>
     <button type="button" class="button button-primary button-next" value="Sincronizar" id="sincronizar_ingressos">Ingressos</button>
     <button type="button" class="button button-primary button-next" value="Sincronizar" id="sincronizar_combos">Combos</button>
     <script>
        jQuery(document).ready(function($) {
            $('#sincronizar_ingressos').on('click', function() {
                const formData = new FormData();
                formData.append('action', 'update_estoque_vouchers_ingresso');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        console.log(data)
                    }
                });
            });
            $('#sincronizar_combos').on('click', function() {
                const formData = new FormData();
                formData.append('action', 'update_estoque_vouchers_combos');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        console.log(data)
                    }
                });
            });
        });
    </script>
    <?php
}

function importar_callback()
{
?>
    <input type="file" id="importar" name="planilha" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
    <div id="modal-transaction" class="modal">
        <div id="load">
            <div class="flex items-center justify-center h-32">
                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
        <div id="resultado_importar" class="mt-2"></div>
        <div id="resultado_conclusao" class="mt-2">
            <div class="max-w-md mx-auto bg-white shadow-md rounded-xl overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="px-4 py-3 w-1/3">Informações sobre a importação</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <tr class="border-b ingressos">
                            <td class="bg-gray-100 font-semibold px-4 py-3 w-1/3">Ingressos não cadastrados</td>
                            <td class="px-4 py-3 ingressos-nao-cadastrados"></td>
                        </tr>
                        <tr class="border-b">
                            <td class="bg-gray-100 font-semibold px-4 py-3">Ingressos inseridos nessa importação</td>
                            <td class="px-4 py-3 ingressos-novos"></td>
                        </tr>
                        <tr class="border-b">
                            <td class="bg-gray-100 font-semibold px-4 py-3">Total de ingressos disponíveis</td>
                            <td class="px-4 py-3 total-ingressos-disponiveis"></td>
                        </tr>
                        <tr class="border-b ingressos">
                            <td class="bg-gray-100 font-semibold px-4 py-3 w-1/3">Combos não cadastrados</td>
                            <td class="px-4 py-3 combos-nao-cadastrados"></td>
                        </tr>
                        <tr class="border-b">
                            <td class="bg-gray-100 font-semibold px-4 py-3">Combos inseridos nessa importação</td>
                            <td class="px-4 py-3 combos-novos"></td>
                        </tr>
                        <tr class="border-b">
                            <td class="bg-gray-100 font-semibold px-4 py-3">Total de combos disponíveis</td>
                            <td class="px-4 py-3 total-combos-disponiveis"></td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
        <div id="resultado_parse" class="mt-2 hidden">
            <div>Foram encontrados <span class="combos font-bold"></span> vouchers de <b>Combos (Pipoca + Refrigerante)</b>:
                <div class="w-full max-w-md mx-auto mt-4">
                    <details class="mb-2 bg-white rounded-xl shadow p-4">
                        <summary class="cursor-pointer font-semibold text-lg">Combos</summary>
                        <div class="combos_parse"></div>
                    </details>
                </div>
            </div>
            <div class="ingressos hidden">
                Foram encontrados <span style="font-weight: bold" class="result"></span> vouchers de <b>Ingressos</b>
                <div class="w-full max-w-md mx-auto mt-4">
                    <details class="mb-2 bg-white rounded-xl shadow p-4">
                        <summary class="cursor-pointer font-semibold text-lg">Ingressos</summary>
                        <div class="ingressos_parse"></div>
                    </details>
                </div>
            </div>
            <div class="my-2">Você tem certeza que deseja importar esses vouchers?</div>
            <div class="text-red-800 font-bold text-center uppercase">Essa ação não pode ser desfeita</div>
            <div class="my-2">
                <form id="importar_vouchers">
                    <div class="flex justify-center gap-2 items-center">
                        <label for="senha">Senha:</label>
                        <input type="password" name="password" id="senha" placeholder="Insira sua senha" required>
                    </div>
                    <input type="text" name="url_planilha" id="url_planilha" placeholder="Url da planilha" class="hidden">
                    <div class="flex justify-center my-2">
                        <button type="submit" id="confirm_import" class="button button-primary" disabled>Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#senha').on('input', function() {
                const senha = $(this).val().trim();
                $('#confirm_import').prop('disabled', senha === '');
            });

            $('#modal-transaction').on('hidden.bs.modal', function() {
                $('#importar').val('');
                console.log('teste')
            });

            $('#importar').on('change', function() {
                const fileInput = this;
                const formData = new FormData();
                formData.append('action', 'importar_planilha_cinemark');
                formData.append('planilha', fileInput.files[0]);
                $('#resultado_importar').html('');
                $('#load').removeClass('hidden');
                $('#resultado_parse').addClass('hidden');
                $('#resultado_conclusao').addClass('hidden');

                $('#modal-transaction').modal();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $('#load').addClass('hidden');
                        $('#resultado_importar').html(data.data.mensagem);
                        $('#resultado_parse .ingressos').addClass('hidden');

                        if (data.success) {
                            $('#resultado_parse').removeClass('hidden');
                            if (data.data.dados.COD_PIPOCA.length != data.data.dados.COD_REFRIGERANTE.length) {
                                $('#resultado_importar').html('Erro ao ler a planilha: Quantidade de vouchers diferentes.');
                                $('#resultado_importar').append('<p>Quantidade de vouchers diferentes e não é possível continuar. <br> Por favor, insira uma planilha com os combos corretos.</p>');
                                return
                            }
                            const combos = data.data.dados.COD_PIPOCA.map((codigo, index) => {
                                return 'PIP:' + codigo + ' - REF: ' + data.data.dados.COD_REFRIGERANTE[index]
                            })
                            $('.combos_parse').html(combos.join('<br>'));
                            $('#resultado_parse .combos').html(data.data.dados.COD_PIPOCA.length)
                            if (data.data.dados.COD_INGRESSO) {
                                $('#resultado_parse .ingressos').removeClass('hidden');
                                $('#resultado_parse .ingressos .result').html(data.data.dados.COD_INGRESSO.length)
                                $('.ingressos_parse').html(data.data.dados.COD_INGRESSO.join(', '))
                            }
                            $('#url_planilha').val(data.data.planilha);
                        }
                    }
                });
            });

            $('#importar_vouchers').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'confirmar_importacao');

                $('#resultado_importar').html('');
                $('#resultado_parse').addClass('hidden');

                $('#resultado_conclusao').removeClass('hidden');

                $('#load').removeClass('hidden');

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $('#load').addClass('hidden');
                        $('#resultado_conclusao').html(data.data.mensagem);
                        if (data.success) {
                            $('#ingressos-nao-cadastrados').html(data.data.ingressos_ja_cadastrados);
                            $('#ingressos-novos').html(data.data.ingressos_cadastrados.length);
                            $('#total-ingressos-disponiveis').html(data.data.ingressos_total);
                            $('#combos-nao-cadastrados').html(data.data.combos_ja_cadastrados);
                            $('#combos-novos').html(data.data.combos_cadastrados.length);
                            $('#total-combos-disponiveis').html(data.data.combos_total);
                        } else {
                            setTimeout(function() {
                                $('#resultado_parse').removeClass('hidden');
                            }, 1000);
                        }
                    }
                });
            });
        });
    </script>

<?php
}


// Callback da seção
function descricao_principal_callback()
{
    echo '<p>As planilhas seguem um padrão específico.</p>';
    echo '<p>Para importar os vouchers, clique no botão "Importar Vouchers".</p>';
    echo '<p>Para baixar o modelo de planilha, clique no botão "Download Modelo".</p>';
}
