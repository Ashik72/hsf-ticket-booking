<?php

if (!defined('ABSPATH'))
  exit;


add_action( 'tf_create_options', 'wp_expert_custom_options_pmpro_nhpa_opts', 150 );

function wp_expert_custom_options_pmpro_nhpa_opts() {


	$titan = TitanFramework::getInstance( 'hsf_tb' );
	$section = $titan->createAdminPanel( array(
		    'name' => __( 'Hex Fight Series Ticket Booking', 'hsf_tb' ),
		    'icon'	=> 'dashicons-networking'
		) );

	$tab = $section->createTab( array(
    		'name' => 'General Options'
		) );

    $tab->createOption([
      'name' => 'Default Seat Img',
      'id' => 'default_seat_color',
      'type' => 'text',
      'default' => hsf_tb_PLUGIN_URL.'img/seat-red.png',
      'desc' => ' Set default seat color. <br><code>'.hsf_tb_PLUGIN_URL.'img/seat-red.png</code>'
      ]);

      $tab->createOption([
        'name' => 'Select Zone Options',
        'id' => 'select_zone_opts',
        'type' => 'textarea',
        'default' => '',
        'desc' => ' Set Zone Options [each per line]'
        ]);

        $tab->createOption([
          'name' => 'Type of Ticket options',
          'id' => 'select_tot_opts',
          'type' => 'textarea',
          'default' => '',
          'desc' => ' Set Type of Ticket options [each per line]'
          ]);

          $tab->createOption([
            'name' => 'Select Seat Options',
            'id' => 'select_seats_opts',
            'type' => 'textarea',
            'default' => '',
            'desc' => ' Set Zone Options [each per line]'
            ]);

            $tab->createOption([
              'name' => 'Warning on user deselect seat',
              'id' => 'warning_text_user_deselect',
              'type' => 'textarea',
              'default' => 'A capacity event is expected, please refrain from leaving gaps between seating. If you have any specific requests or you would like information about purchasing a table or skybox please call 0499 282 098',
              'desc' => ' Set warning text on user deselect seat'
              ]);

		$section->createOption( array(
  			  'type' => 'save',
		) );

}


 ?>
