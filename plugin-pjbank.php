<?php
/*
Plugin Name: Boleto PJBank
Plugin URI:  https://pjbank.com.br
Description: Plugin de integração e geração de boletos com o PJBank.
Version:     1.0
Author:      Lucas Martim
Author URI:  https://linkedin.com/in/lmartim
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/


function some_custom_checkout_field_boleto($checkout){
	$user_id = get_current_user_id();

	$options = get_option('woocommerce_pjbank_boleto_settings');
	$enabled_boleto = $options['enabled'];
	$cpf_cnpj = get_user_meta( $user_id, '_cpf_cnpj', true );

	?>

		<script>
			jQuery(document).ready(function($){
				var enabled_boleto = '<?php echo $enabled_boleto ?>';
				var cpf_cnpj = '<?php echo $cpf_cnpj ?>';

				if(enabled_boleto == 'no'){
					enabled_boleto = false;
				}else{
					enabled_boleto = true;
				}

				console.log("Boleto: "+enabled_boleto);

				if(enabled_boleto){
					$( 'body' ).on( 'updated_checkout', function() {
						$("#billing_last_name_field").after('\
							<p class="form-row my-field-class form-row-wide validate-required">\
								<label for="cpf_cnpj" class="">CPF/CNPJ <abbr class="required" title="obrigatório">*</abbr></label>\
								<input type="text" class="input-text " name="cpf_cnpj" id="cpf_cnpj" placeholder="" value="'+cpf_cnpj+'">\
							</p>'
						);

						$(".payment_method_pjbank_boleto").append('<input type="hidden" class="post_boleto" name="post_boleto" value="false">');

						if($('#payment_method_pjbank_boleto').is(':checked')) {
							$(".post_boleto").val("true");
						}

						$(".payment_methods .input-radio").on('click', function(e){	
							if($('#payment_method_pjbank_boleto').is(':checked')) {
								$(".post_boleto").val("true");
							}else{
								$(".post_boleto").val("false");
							}
						})
					})
				}
			})
		</script>

	<?php

}
add_action( 'woocommerce_after_order_notes', 'some_custom_checkout_field_boleto' );

function some_custom_checkout_field_process_boleto() {
	
	if($_POST['post_boleto'] == 'true'){
		if (!$_POST['cpf_cnpj']){
			wc_add_notice( __( 'Insira o CPF/CNPJ.' ), 'error' );
		}
	}
}
add_action('woocommerce_checkout_process', 'some_custom_checkout_field_process_boleto');

function some_custom_checkout_field_update_order_meta_boleto($order_id ) {
	$user_id = get_current_user_id();
	
	if ( ! empty( $_POST['cpf_cnpj'] ) ) {
		update_user_meta( $user_id, '_cpf_cnpj', sanitize_text_field( $_POST['cpf_cnpj'] ) );
		update_post_meta( $order_id, 'CPF/CNPJ', sanitize_text_field( $_POST['cpf_cnpj'] ) );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'some_custom_checkout_field_update_order_meta_boleto' ); 

function init_pjbank_getway_boleto(){
    require_once("WC_PJBank_Gateway_Boleto.php");
}
add_action( 'wp_loaded', 'init_pjbank_getway_boleto' );

function add_pjbank_gateway_class_boleto( $methods ) { 
	$methods[] = 'WC_PJBank_Gateway_Boleto';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_pjbank_gateway_class_boleto' );

function your_custom_field_function_name_boleto($order){

	$pj_boleto = get_post_meta( $order->get_id(), '_pj_boleto', true);

	if($pj_boleto){
		$nosso_numero = get_post_meta($order->get_id(), '_nosso_numero', true);
		if(isset($nosso_numero[0])){
			echo "<p><b style='color: #333'>Nosso Número:</b><br /> " . $nosso_numero . "</p>";
		}else{
			echo "<p><b style='color: #333'>Nosso Número:</b><br /> Não identificado</p>";
		}
	
		$link_boleto = get_post_meta( $order->get_id(), '_link_boleto', true);
		if(isset($link_boleto[0])){
			echo "<p><b style='color: #333'>Link para o boleto:</b><br /><a href='". $link_boleto ."' target='_blank'> Clique aqui para visualizar</a></p>";
		}else{
			echo "<p><b style='color: #333'>Link para o boleto:</b><br /> Não identificado</p>";
		}
	}

}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'your_custom_field_function_name_boleto', 10, 1 );

function custom_popup_boleto_boleto($order){
	global $woocommerce;
	$link_boleto = get_post_meta( $order, '_link_boleto' );
	$post_boleto = get_post_meta( $order, '_post_boleto');

	if(($post_boleto)&&(isset($link_boleto[0]))){ ?>
		
		<div class="highlight">
			<a href="<?php echo $link_boleto[0] ?>" target="_blank">Clique aqui</a> para visualizar o boleto
		</div>
		<style>
			.highlight{
				padding: 9px 14px;
				margin-bottom: 14px;
				background-color: #f7f7f9;
				border: 1px solid #e1e1e8;
				border-radius: 4px;
			}
		</style>
		<script>
			var div = "<?php echo $link_boleto[0] ?>";
			setTimeout(function () {
				window.open(div); 
			}, 800); 
		</script>

	<?php 
	}
}
add_action( 'woocommerce_thankyou', 'custom_popup_boleto_boleto');
