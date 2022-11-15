<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Html for newslterr form
 *
 * @package Guide requests
 * @since 1.0.0
 */
class Wpens_Public {
	
	public function __construct(){
		
		add_action( 'wp_ajax_wpens_add_newsletter'	,array( $this, 'wpens_add_newsletter' ) );
		add_action( 'wp_ajax_nopriv_wpens_add_newsletter',array( $this,'wpens_add_newsletter' ) ) ;
	}

	/**
	 * Validating and insert
	 *
	 * Validate whole form and insert data into database
	 *
	 * @package Guide requests
	 * @since 1.0.0
	 */
	public function wpens_add_newsletter() {
		global $wpdb;
		$data = urldecode( $_POST['datos'] );
		if ( !empty( $data ) ) :
			$data_array = explode( "&", $data );
			$fields = [];
			foreach ( $data_array as $array ) :
				$array = explode( "=", $array );
				$fields[ $array[0] ] = $array[1];
			endforeach;
		endif;
 
		// $input_nombre  = isset( $_POST['input_nombre'] ) ? sanitize_text_field( $_POST['input_nombre'] ) : '';
		// $input_correo  = isset( $_POST['input_correo'] ) ? sanitize_text_field( $_POST['input_correo'] ) : '';
		// $input_sitio_web  = isset( $_POST['input_sitio_web'] ) ? sanitize_text_field( $_POST['input_sitio_web'] ) : '';
		// $input_empresa  = isset( $_POST['input_empresa'] ) ? sanitize_text_field( $_POST['input_empresa'] ) : '';
		// $guide_type  = isset( $_POST['type_resource'] ) ? sanitize_text_field( $_POST['type_resource'] ) : '';
		$ip 		= isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
		$fields['in_newsletter'] = isset( $fields['in_newsletter'] ) ? 1 : 0;
        
		$response = array();

		// Obtenga ID de correo electrónico si está registrado
		$table_name = $wpdb->prefix . 'ens_subscribers';
		$myrows = $wpdb->get_results( "SELECT email FROM ".$table_name." WHERE email = '".$fields['input_correo']."'" );
		 
		// insert newslertter data 
		$wpdb->insert( 
			    $table_name, 
				    array( 
				        'full_name'	=> $fields['input_nombre'],
				        'email'			=> $fields['input_correo'],
				        'company_website'		=> $fields['input_sitio_web'],
				        'company_name'		=> $fields['input_empresa'],
				        'in_newsletter'		=> $fields['in_newsletter'],
				        'nucleo_empleados'		=> $fields['nucleo_empleados'],
				        'guide_type'		=> $fields['type_resource'],
				        'user_ip'		=> $ip,
				        'date'			=> current_time( 'mysql' )
				    ),
				    array( 
						'%s', 
						'%s',
						'%s',
						'%s', 
						'%s',
						'%s',
						'%s'
					)
				);
		
		// Check if data inserted
		if( !empty($wpdb) && !is_wp_error($wpdb) ) {

			$response['status'] = true;
			$response['user_ip'] = base64_encode(tas_get_user_IP());
			$response['errmsg'] = __( 'You have subscribed successfully!.', 'wpens' );
			
		}

		echo json_encode($response);
		exit;
	}
}

return new Wpens_Public();