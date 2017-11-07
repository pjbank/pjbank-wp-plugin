<?php
/*
Plugin Name: Boleto PJBank
Plugin URI:  https://pjbank.com.br
Description: Plugin de integração e geração de boletos com o PJBank.
Version:     1.0
Author:      PJBank
Author URI:  https://pjbank.com.br
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/


function some_custom_checkout_field($checkout){
	$user_id = get_current_user_id();
	
	woocommerce_form_field( 'cpf_cnpj', array(
		'type'         => 'text',
		'class'         => array('my-field-class form-row-wide'),
		'label'         => __('CPF/CNPJ'),
		'required'     => true,
	), get_user_meta( $user_id, '_cpf_cnpj', true ));
}
add_action( 'woocommerce_after_order_notes', 'some_custom_checkout_field' );

function some_custom_checkout_field_process() {

	if (!$_POST['cpf_cnpj'])
		wc_add_notice( __( 'Insira o CPF/CNPJ.' ), 'error' );

}
add_action('woocommerce_checkout_process', 'some_custom_checkout_field_process');

function some_custom_checkout_field_update_order_meta($order_id ) {
	$user_id = get_current_user_id();
	
	if ( ! empty( $_POST['cpf_cnpj'] ) ) {
		update_user_meta( $user_id, '_cpf_cnpj', sanitize_text_field( $_POST['cpf_cnpj'] ) );
		update_post_meta( $order_id, 'CPF/CNPJ', sanitize_text_field( $_POST['cpf_cnpj'] ) );
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'some_custom_checkout_field_update_order_meta' );

function init_pjbank_getway(){
    require_once("WC_PJBank_Gateway.php");
}
add_action( 'plugins_loaded', 'init_pjbank_getway' );

function add_pjbank_gateway_class( $methods ) { 
	$methods[] = 'WC_PJBank_Gateway';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_pjbank_gateway_class' );

function your_custom_field_function_name($order){
	// var_dump(get_post_meta( $order->id, '_nosso_numero' ));
	$nosso_numero = get_post_meta( $order->id, '_nosso_numero' );
	if($nosso_numero[0] > 0){
		echo "<p><b style='color: #333'>Nosso Número:</b><br /> " . $nosso_numero[0] . "</p>";
	}else{
		echo "<p><b style='color: #333'>Nosso Número:</b><br /> Não identificado</p>";
	}

	$link_boleto = get_post_meta( $order->id, '_link_boleto' );
	if($link_boleto[0] != null ){
		echo "<p><b style='color: #333'>Link para o boleto:</b><br /><a href='". $link_boleto[0] ."' target='_blank'> Clique aqui para visualizar</a></p>";
	}else{
		echo "<p><b style='color: #333'>Link para o boleto:</b><br /> Não identificado</p>";
	}

}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'your_custom_field_function_name', 10, 1 );

function custom_popup_boleto($order){
	global $woocommerce;
	$link_boleto = get_post_meta( $order, '_link_boleto' );

	// $order = new WC_Order($order_id);
	// $items = $order->get_items();

	// $texto = '<table>';
	// 	$text .= '<th><td>Produto</td><td>Preço</td></th>';
	// 	foreach($items as $key){
	// 		$texto .= '<tr>';
	// 		$texto .= '<td>'.$key['name'] . ' X ' . $key['item_meta']['_qty'][0] . ' </td><td> '.$key['item_meta']['_line_total'][0].'</td>';
	// 		$texto .= "</tr>";
	// 	}
	// $texto .= '</table>';

	?>

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
add_action( 'woocommerce_thankyou', 'custom_popup_boleto');
