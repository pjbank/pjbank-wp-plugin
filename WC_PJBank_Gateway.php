<?php

class WC_PJBank_Gateway extends WC_Payment_Gateway {
    function __construct(){
        // define configurações basicas do plugin
        $this->id = "pjbank";
        $this->method_title = "PJBank";
        $this->method_description = "Metodo de pagamento PJBank";
        $this->has_fields = false;

        //funções hierarquicas do woocommerce
        $this->init_form_fields();
        $this->init_settings();

        //os campos que foram iniciados no meu plugin
        $this->title = $this->settings['title'];
        $this->token = $this->settings['token'];

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields(){
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Habilitar/Desabilitar', 'woocommerce' ),
                'type' => 'checkbox',
                'label' => __( 'Pagamento Habilitado', 'woocommerce' ),
                'default' => 'no'
            ),
            'homologacao' => array(
                'title' => __( 'Ambiente Homologação ?'),
                'type' => 'checkbox',
                'description' => __( 'Configura plugin para usar ambiente de SANDBOX', 'woocommerce'),
                'default' => 'yes',
            ),
            'token' => array(
                'title' => __( 'Token', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Adicione o token', 'woocommerce' ),
                'default' => __( '', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'title' => array(
                'title' => __( 'Título', 'woocommerce' ), 
                'type' => 'text', 
                'description' => __( 'Adicione um titulo para seu metodo de pagamento', 'woocommerce' ),
                'default' => __( 'Boleto Bancário', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'vencimento' => array(
                'title' => __( 'Vencimento', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Quantos dias até o vencimento do boleto.', 'woocommerce' ),
                'default' => __( '0', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'juros' => array(
                'title' => __( 'Juros', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Taxa de juros ao dia por atraso. Casas decimais devem ser separadas por ponto, máximo de 2 casas decimais, não enviar caracteres diferentes de número ou ponto. Não usar separadores de milhares. length (1-2)', 'woocommerce' ),
                'default' => __( '0.0', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'multa' => array(
                'title' => __( 'Multa', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Taxa de multa por atraso. Casas decimais devem ser separadas por ponto, máximo de 2 casas decimais, não enviar caracteres diferentes de número ou ponto. Não usar separadores de milhares. Exemplo: 0.98', 'woocommerce' ),
                'default' => __( '0.0', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'desconto' => array(
                'title' => __( 'Desconto', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Valor do desconto por pontualidade, em Reais. Casas decimais devem ser separadas por ponto, máximo de 2 casas decimais, não enviar caracteres diferentes de número ou ponto. Não usar separadores de milhares. Exemplo: 9.58', 'woocommerce' ),
                'default' => __( '0.0', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'logo' => array(
                'title' => __( 'Logo', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'URL do logo da empresa. Essa imagem deve ser PNG, GIF ou JPG. Os tamanhos ideais para envio são 120x80 ou 80x80.', 'woocommerce' ),
                'default' => __( '', 'woocommerce' ),
                'desc_tip'      => true,
            ),
            'webhook' => array(
                'title' => __( 'URL Webhook'),
                'type' => 'text',
                'description' => __( 'URL que será chamada em caso de alterações na transação (consultar documentação do PJBank)', 'woocommerce'),
                'desc_tip' => true,
            ),
        );
    }

    public function process_payment($order_id){
        global $woocommerce;
        $order = wc_get_order( $order_id );
        $total = $order->order_total;
        $cpf_cnpj = $_POST['cpf_cnpj'];

        // Busca o usuário logado e as configurações do Plugin
        $current_user = wp_get_current_user();
        $user_id = get_current_user_id();
        $options = get_option('woocommerce_pjbank_settings');

        // Calcula data de vencimento
        $date = date('Y-m-d');
        $vencimento = $options['vencimento'];
        $vencimento = date('m/d/Y', strtotime(date('Y-m-d') . ' + '.$vencimento.' days'));

        // Reduce stock levels
        // $order->reduce_order_stock();

        // Remove cart
        // $woocommerce->cart->empty_cart();

        // Monta o array de composição de items do boleto
        $composicoes = '';
        $item = 0;
        $items = $order->get_items();

        foreach($items as $key => $value){
            $composicoes[$item]["item_descricao"] = $value['name'];
            $composicoes[$item]["item_quantidade"] = $value['item_meta']['_qty'][0];
            $composicoes[$item]["item_valor"] = $value['item_meta']['_line_total'][0];
            $item += 1; 
        }
        $composicoes = json_encode($composicoes);
        $api = $options["homologacao"] ? "sandbox" : "api";
        // Inicia chamada cURL
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://".$api.".pjbank.com.br/recebimentos/".$options['token']."/transacoes",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",            
            CURLOPT_POSTFIELDS => '{
                "vencimento": "'.$vencimento.'",
                "valor": "'.$total.'",
                "juros": "'.$options['juros'].'",
                "multa": "'.$options['multa'].'",
                "desconto": "'.$options['desconto'].'",
                "nome_cliente": "'.get_user_meta( $user_id, 'billing_first_name', true ).' '.get_user_meta( $user_id, 'billing_last_name', true ).'",
                "cpf_cliente": "'.$cpf_cnpj.'",
                "endereco_cliente": "'.get_user_meta( $user_id, 'billing_address_1', true ).'",
                "numero_cliente": "",
                "complemento_cliente": "'.get_user_meta( $user_id, 'billing_address_2', true ).'",
                "bairro_cliente": "",
                "cidade_cliente": "'.get_user_meta( $user_id, 'billing_city', true ).'",
                "estado_cliente": "'.get_user_meta( $user_id, 'billing_state', true ).'",
                "cep_cliente": "'.get_user_meta( $user_id, 'billing_postcode', true ).'",
                "logo_url": "'.$options['logo'].'",
                "composicoes": '.$composicoes.',
                "pedido_numero": "'.$order_id.'",
                "webhook" => "'.$this->get_option('webhook').'"  
            }',
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            ),
        )); 
        // Retorno da API é salvo no $response
        $response = curl_exec($curl);
        curl_close($curl);
        // FIM - Chamada da API para gerar o boleto

        // Adiciona custom note no pedido, com o JSON que retorna da API
        // $order->add_order_note("composicao:".$composicao);
        $order->add_order_note($response);

        // Decodifica o JSON, para poder manipular no foreach, para ter acesso aos dados
        $response = json_decode($response);

        foreach ($response as $key => $value) {
            // $order->add_order_note($key, $value);
            if($key == 'status'){
                if($value == 500){
                    wc_add_notice( __('Erro de pagamento: ', 'woothemes') . 'Entre em contato com o administrador, para verificar o problema.', 'error' );
                }
            }

            // Salva o 'nosso numero' dentro do pedido, junto com as informações de cobrança
            if($key == 'nossonumero'){
                update_post_meta( $order_id, '_nosso_numero', $value );
            }
            // Envia o link do boleto para o usuário
            if($key == 'linkBoleto'){

                update_post_meta( $order_id, '_link_boleto', $value );

                // funções para disparo de e-mail.
                // Ele pega as informações do usuário logado e recupera o e-mail dele.
                // Abaixo, é montado o restante das informações do e-mail, para realizar o disparo.
                // $to =  $current_user->user_email;
                // $subject = 'teste';
                // $body = 'Link para o boleto: ' . $value;
                // $headers = array('Content-Type: text/html; charset=UTF-8');
                // $mailResult = wp_mail($to, $subject, $body);
                // $order->add_order_note('to:'.$to);
                // $order->add_order_note('subject:'.$subject);
                // $order->add_order_note('headers:'.$headers);
                // $order->add_order_note('body:'.$body);
                // $order->add_order_note('email:'.$mailResult);
            }
        }
        // Mark as on-hold (we're awaiting the cheque)
        // $order->update_status('on-hold', __( 'Awaiting cheque payment', 'woocommerce' ));
        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }

    public function admin_options(){
        echo '<h3>'.__('PJBank', 'woocommerce').'</h3>';
        echo '<p>'.__('Gere boletos usando PJBank.').'</p>';
        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this -> generate_settings_html();
        echo '</table>';
    }

}
