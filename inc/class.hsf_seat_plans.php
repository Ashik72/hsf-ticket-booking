<?php

if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

/**
 * HSF Seat Plans
 */
class HSF_Seat_Plans
{

  private static $instance;

  public static function get_instance() {
  	if ( ! isset( self::$instance ) ) {
  		self::$instance = new self();
  	}

  	return self::$instance;
  }

  function __construct()
  {

    add_action( 'init', [$this, 'hsf_tb_seat_plans_func'], 0 );
    add_action( 'init', [$this, 'meta_boxes']);
		add_action( 'wp_enqueue_scripts', [$this, 'check_and_add_hsf_class'] );
    add_filter( 'woocommerce_product_data_tabs', [$this, 'hsf_data_tab'] );
    add_action( 'woocommerce_product_data_panels', [$this, 'hsf_data_tab_data'] );
    add_action( 'woocommerce_process_product_meta', array( &$this, 'hsf_data_tab_data_meta_save' ) );
    add_action( 'wp', array( $this, 'modify_wc_product' ) );
		add_action( 'wp_enqueue_scripts', array($this, 'load_custom_wp_frontend_style') );
		add_action( 'wp_ajax_processFormData', array($this, 'processFormDataSubmit') );
		add_action( 'wp_ajax_nopriv_processFormData', array($this, 'processFormDataSubmit') );
		add_action( 'woocommerce_thankyou', [$this, 'add_hsf_data'], 10 );
		add_action( 'woocommerce_before_calculate_totals', [$this, 'add_custom_price'] );

		add_action( 'wp_ajax_processFormDataQue', array($this, 'processFormDataQueSubmit') );
		add_action( 'wp_ajax_nopriv_processFormDataQue', array($this, 'processFormDataQueSubmit') );

		//add_action( 'wp_footer', array( $this, 'testFooter' ) );

		add_action( 'wp_ajax_bottomSelectZoneGetRow', array($this, 'bottomSelectZoneGetRowSubmit') );
		add_action( 'wp_ajax_nopriv_bottomSelectZoneGetRow', array($this, 'bottomSelectZoneGetRowSubmit') );

		add_action( 'wp_ajax_zone_c_row_get_seats', array($this, 'zone_c_row_get_seatsSubmit') );
		add_action( 'wp_ajax_nopriv_zone_c_row_get_seats', array($this, 'zone_c_row_get_seatsSubmit') );

		add_filter( 'woocommerce_locate_template', [$this, 'myplugin_woocommerce_locate_template'], 10, 3 );

		add_action( 'widgets_init', [$this, 'hsf_ticket_widgets'] );

		//add_action( 'template_redirect', [$this, 'clear_sold_seats'] );

		//add_action( 'admin_post_clear_sold_seats', [$this, 'clear_sold_seats'] );
		//add_action( 'admin_post_nopriv_clear_sold_seats', [$this, 'clear_sold_seats'] );

		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_func') );

		add_action( 'wp_ajax_clear_sold_seats', array($this, 'clear_sold_seats') );
		add_action( 'wp_ajax_nopriv_clear_sold_seats', array($this, 'clear_sold_seats') );

		add_action( 'wp_ajax_clear_sold_seats_individually', array($this, 'clear_sold_seats_individually') );
		add_action( 'wp_ajax_nopriv_clear_sold_seats_individually', array($this, 'clear_sold_seats_individually') );

		add_action( 'wp_ajax_processFormDataQueAdmin', array($this, 'processFormDataQueAdmin') );
		add_action( 'wp_ajax_nopriv_processFormDataQueAdmin', array($this, 'processFormDataQueAdmin') );


  }

	public function processFormDataQueAdmin() {

		if (empty($_POST['formData']))
			wp_die();

		if (empty($_POST['postID']))
			wp_die();

			global $woocommerce;

			$postID = $_POST['postID'];

			$queData = $_POST['formData'];

				$price = (float) 0;

				$general_admission_count = 0;

				foreach ($queData as $single_key => $queDataSingle) {

					$price += (float) $queDataSingle["price"];

					if (!empty($queDataSingle["general_admission"]))
						$general_admission_count++;
					// $data_line = $queDataSingle['name']." - Seat no. - ".$queDataSingle['seat_number']." @ $".$queDataSingle['price']." (UID: {$queDataSingle['uid']})";
					//
					// echo json_encode($data_line);

				}

				$hsf_seating_data = ( is_array($queData) ? $queData : [] );

				$_hsf_seats_sold = get_post_meta( $postID, '_hsf_seats_sold', true);


				$_hsf_seats_sold = (is_array($_hsf_seats_sold) ? $_hsf_seats_sold : []);

				foreach ($hsf_seating_data as $key => $queDataSingle) {
					array_push($_hsf_seats_sold, $queDataSingle['uid']);
				}

				$update_post_meta = update_post_meta($_POST['postID'], '_hsf_seats_sold', $_hsf_seats_sold);

				echo json_encode( ( empty($update_post_meta) ? 0 : 1 ) );

		wp_die();
	}

	public function clear_sold_seats_individually() {

		if (empty($_POST['postID']))
			wp_die();

			$seats_to_unblock = $_POST['seat_data'];

			$sold_seats = get_post_meta($_POST['postID'],  '_hsf_seats_sold', true);

			$new_seats = [];

			foreach ($sold_seats as $key => $sold_seats_single) {

				if (in_array($sold_seats_single, $seats_to_unblock))
					continue;

				$new_seats[] = $sold_seats_single;

			}

			$update_post_meta = update_post_meta($_POST['postID'], '_hsf_seats_sold', $new_seats);

			echo json_encode( ( empty($update_post_meta) ? 0 : 1 ) );
			wp_die();


	}

	public function admin_enqueue_scripts_func() {

		wp_register_script( 'hsf_tb-admin-script', hsf_tb_PLUGIN_URL.'js/admin.js', array( 'jquery' ), '', true );

		wp_localize_script( 'hsf_tb-admin-script', 'hsf_tb_admin_plugin_data', array( 'ajax_url' => admin_url('admin-ajax.php'), 'userID' => get_current_user_id() ));

		wp_enqueue_script( 'hsf_tb-admin-script' );


	}


	public function clear_sold_seats() {

		if (empty($_POST['postID']))
			wp_die();


		if (empty($_POST['userID']))
			wp_die();

		if (!current_user_can( 'manage_options' ))
			wp_die();

		echo json_encode(delete_post_meta($_POST['postID'],  '_hsf_seats_sold'));

		wp_die();

		//d($_POST);

	}

	public function check_and_add_hsf_class() {

		$hsf_seat = get_post_meta(get_the_ID(), '_hsf_seat_selected', true);

    if (empty($hsf_seat))
      return;

    if (strcmp($hsf_seat, "none") == 0)
	  return;


			?>

			<script>

    window.onload = function () {
			jQuery("#et-main-area").addClass("hsf_ticket_page");

		}

			</script>

			<?php

	}

	function hsf_ticket_widgets() {

	register_sidebar( array(
		'name'          => 'HSF Ticketing Left Sidebar',
		'id'            => 'hsf_ticket_left',
		'before_widget' => '<div class="hsf_div_left">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => 'HSF Ticketing Right Sidebar',
		'id'            => 'hsf_ticket_right',
		'before_widget' => '<div class="hsf_div_right">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="rounded">',
		'after_title'   => '</h2>',
	) );

}

	function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {

		$hsf_seat = get_post_meta(get_the_ID(), '_hsf_seat_selected', true);


    if (empty($hsf_seat))
			return $template;

    if (strcmp($hsf_seat, "none") == 0)
			return $template;


	  global $woocommerce;



	  $_template = $template;

	  if ( ! $template_path ) $template_path = $woocommerce->template_url;

	  $plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';

	  // Look within passed path within the theme - this is priority

	  $template = locate_template(

	    array(

	      $template_path . $template_name,

	      $template_name

	    )

	  );



	  // Modification: Get the template from this plugin, if it exists

	  if ( ! $template && file_exists( $plugin_path . $template_name ) )

	    $template = $plugin_path . $template_name;



	  // Use default template

	  if ( ! $template )

	    $template = $_template;



	  // Return what we found
	  return $template;

	}

	public function zone_c_row_get_seatsSubmit() {

		if (empty($_POST['postID']))
			wp_die();


		$row_title = $_POST['row_title'];
		$row_price = $_POST['row_price'];
		$row_qty = (int) $_POST['row_qty'];

			$uid_string = str_replace(' ', '-', $row_title); // Replaces all spaces with hyphens.
			$uid_string = preg_replace('/[^A-Za-z0-9\-]/', '', $uid_string); // Removes special chars.

		$seat_data = [];

		for ( ; $row_qty > 0; $row_qty--) {
			$seat_uid = 	$uid_string.((int) $row_price)."_".$row_qty;
			$seat_number = $row_qty;
			$seat_title = $row_title;

			$seat_data[] = [ $seat_uid , $seat_number , $seat_title ];

		}

		echo json_encode($seat_data);

		wp_die();


	}

	public function bottomSelectZoneGetRowSubmit() {

		if (empty($_POST['zone_val']))
			wp_die();

		if (empty($_POST['postID']))
			wp_die();

		$zone_val = $_POST['zone_val'];
		$postID = $_POST['postID'];

		$seating_plans = $this->seating_plans_data_array($postID);

		if (empty($seating_plans[$zone_val]))
			wp_die();

			$seating_plans = $seating_plans[$zone_val];

			if ($zone_val == "general_seating_plan") {

				$_hsf_seats_sold = get_post_meta( $postID, '_hsf_seats_sold', true);

				$count_general_seat = 0;
				$output_array_single_sold_seat = 0;
				foreach ($_hsf_seats_sold as $key => $single_sold_seat) {
					preg_match("/general_admission/", $single_sold_seat, $output_array_single_sold_seat);

					if (!empty($output_array_single_sold_seat)) {
						$count_general_seat++;
						$output_array_single_sold_seat = 0;
					}

				}

				$seating_plans_qty = ((int) $seating_plans['qty']) - $count_general_seat;

				echo json_encode(['general_seating' => 1, 'price' => $seating_plans['price'], 'plan_qty' => $seating_plans_qty, "plan_zone" => $seating_plans['title']]);
				wp_die();
			}


			$title = $seating_plans['title'];
			$seating_plans['plan_data'] = explode(PHP_EOL, $seating_plans['plan_data']);
			$plan_data = [];
			$price = [];
			$plan_qty = [];

			$seating_plans['plan'] = explode(PHP_EOL, $seating_plans['plan']);




			foreach ($seating_plans['plan_data'] as $key => $plan_data_price) {
				$plan_data_price = explode("|", $plan_data_price);
				$plan_data[] = $plan_data_price[0];
				$price[] = (int) $plan_data_price[1];
			}

			foreach ($seating_plans['plan'] as $key => $plan_data_qty) {
				$plan_qty[] = (int) $plan_data_qty;
			}



			echo json_encode(['plan_data' => $plan_data, 'price' => $price, 'plan_qty' => $plan_qty, "plan_zone" => $title]);

			wp_die();

}


	function add_custom_price( $cart_object ) {
		if (empty($_COOKIE['hsf_seating_data']))
			return;

			$_COOKIE['hsf_seating_data'] = str_replace('\\', '', $_COOKIE['hsf_seating_data']);
			$_COOKIE['hsf_seating_data'] = maybe_unserialize($_COOKIE['hsf_seating_data']);


	    $custom_price = $_COOKIE['hsf_seating_data']['price_fragment']; // This will be your custome price
	    foreach ( $cart_object->cart_contents as $key => $value ) {

	        	$value['data']->price = $custom_price;

	    }
	}


	public function add_hsf_data($order_id) {
		if ( ! $order_id ) return;

		if (empty($_COOKIE['hsf_seating_data']))
			return;

		//$_COOKIE['hsf_seating_data'] = maybe_unserialize($_COOKIE['hsf_seating_data']);
		$_COOKIE['hsf_seating_data'] = str_replace('\\', '', $_COOKIE['hsf_seating_data']);
		$_COOKIE['hsf_seating_data'] = maybe_unserialize($_COOKIE['hsf_seating_data']);
		$hsf_seating_data = ( is_array($_COOKIE['hsf_seating_data']['queData']) ? $_COOKIE['hsf_seating_data']['queData'] : [] );
		$WC_Order = new WC_Order($order_id);

		$_hsf_seats_sold = get_post_meta( $_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold', true);
		$_hsf_seats_sold_general_admission = get_post_meta( $_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold_general_admission', true);
		$_hsf_seats_sold_general_admission = (int) $_hsf_seats_sold_general_admission;

		$_hsf_seats_sold = (is_array($_hsf_seats_sold) ? $_hsf_seats_sold : []);

		foreach ($hsf_seating_data as $key => $queDataSingle) {

			$data_line = $queDataSingle['name']." - Seat no. - ".$queDataSingle['seat_number']." @ $".$queDataSingle['price']." (UID: {$queDataSingle['uid']})";
			array_push($_hsf_seats_sold, $queDataSingle['uid']);
			$WC_Order->add_order_note($data_line);
		}

			$general_admission = (int) $_COOKIE['hsf_seating_data']['general_admission'];

			$general_admission += $_hsf_seats_sold_general_admission;

			$general_admission = update_post_meta($_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold_general_admission', $general_admission);

			$_hsf_seats_sold = update_post_meta($_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold', $_hsf_seats_sold);
	}


	public function testFooter() {

		d(get_post_meta( get_the_ID(), '_hsf_seats_sold', true));
		d(get_post_meta( get_the_ID(), 'test_admin', true));

		return;

		$cData = [ 'test' => 2, 'test3' => 3 ];
		//d($cData);
		//d(WC()->cart->add_to_cart(38, 3, '' , [], $cData));
		//d(WC()->cart);
		//update_post_meta( 43, 'my_custom_meta_key', 'my data' );
		$_COOKIE['hsf_seating_data'] = str_replace('\\', '', $_COOKIE['hsf_seating_data']);


		$_COOKIE['hsf_seating_data'] = maybe_unserialize($_COOKIE['hsf_seating_data']);

		$hsf_seating_data = ( is_array($_COOKIE['hsf_seating_data']['queData']) ? $_COOKIE['hsf_seating_data']['queData'] : [] );

		$_hsf_seats_sold = get_post_meta( $_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold', true);
		$general_admission = get_post_meta( $_COOKIE['hsf_seating_data']['postID'], '_hsf_seats_sold_general_admission', true);

		d($_hsf_seats_sold);
		d($general_admission);



	}

	public function processFormDataQueSubmit() {

		if (empty($_POST['formData']))
			wp_die();

		if (empty($_POST['postID']))
			wp_die();

			global $woocommerce;

			$postID = $_POST['postID'];

			$queData = $_POST['formData'];

				$price = (float) 0;

				$general_admission_count = 0;

				foreach ($queData as $single_key => $queDataSingle) {

					$price += (float) $queDataSingle["price"];

					if (!empty($queDataSingle["general_admission"]))
						$general_admission_count++;
					// $data_line = $queDataSingle['name']." - Seat no. - ".$queDataSingle['seat_number']." @ $".$queDataSingle['price']." (UID: {$queDataSingle['uid']})";
					//
					// echo json_encode($data_line);

				}



			$add_to_cart = WC()->cart->add_to_cart($postID, count($queData));

			setcookie('hsf_seating_data', '', time() - 3600, '/');

			$set_cookie = setcookie('hsf_seating_data', serialize([
				'queData' => $queData,
				'price' => $price,
				'price_fragment' => (float) ($price / count($queData)),
				'postID' => $postID,
				'general_admission' => $general_admission_count

			]), time()+(60*60*24), '/');

		$checkout_url = $woocommerce->cart->get_checkout_url();

		echo json_encode($checkout_url);

		wp_die();
	}

	public function processFormDataSubmit() {

		if (empty($_POST['formData']))
			wp_die();

			global $woocommerce;

		$formData = $_POST['formData'];

		$zone_opts = "";
		$tot_opts = "";
		$seating_opts = "";
		$quantity_seats = "";
		$seating_product_id = "";

		$final_price = 0;
		$final_price_temp = 0;

		foreach ($formData as $key => $formDataSingle) {


			if ($formDataSingle['name'] == "zone_opts") {
				$zone_opts = $formDataSingle['value'];
				preg_match_all('!\d+!', $formDataSingle['value'], $final_price_temp);
				$final_price_temp = (float) $final_price_temp[0][0];
				$final_price += $final_price_temp;

			}

			if ($formDataSingle['name'] == "tot_opts") {
				$tot_opts = $formDataSingle['value'];

				preg_match_all('!\d+!', $formDataSingle['value'], $final_price_temp);
				$final_price_temp = (float) $final_price_temp[0][0];
				$final_price += $final_price_temp;

			}

			if ($formDataSingle['name'] == "seating_opts") {

				$seating_opts = $formDataSingle['value'];

								preg_match_all('!\d+!', $formDataSingle['value'], $final_price_temp);
								$final_price_temp = (float) $final_price_temp[0][0];
								$final_price += $final_price_temp;


			}

			if ($formDataSingle['name'] == "quantity_seats")
				$quantity_seats = $formDataSingle['value'];

			if ($formDataSingle['name'] == "seating_product_id")
				$seating_product_id = $formDataSingle['value'];

		}
		//$final_price = ( empty($quantity_seats) ) ? ($final_price*1) : ($final_price*$quantity_seats) ;

		if (empty($seating_product_id))
			wp_die();

			$init_product = new WC_Product($seating_product_id);
			$price = $init_product->get_price();

			$add_to_cart = WC()->cart->add_to_cart($seating_product_id, $quantity_seats);

			$set_cookie = setcookie('hsf_seating_data', serialize([
				'Zone Option' => $zone_opts,
				'Type of Ticket Option' => $tot_opts,
				'Seating Option' => $seating_opts,
				'Quantity' => $quantity_seats,
				'price' => $final_price

			]), time()+(60*60*24), '/');

$checkout_url = $woocommerce->cart->get_checkout_url();
_e(json_encode($checkout_url));
		wp_die();
	}

	public function load_custom_wp_frontend_style() {

		wp_register_script( 'hsf_tb-script', hsf_tb_PLUGIN_URL.'js/script.js', array( 'jquery' ), '', true );

		$_hsf_seats_sold = get_post_meta( get_the_ID(), '_hsf_seats_sold', true);
		$background_img = hsf_tb_PLUGIN_URL."img/background.png";
		wp_localize_script( 'hsf_tb-script', 'hsf_tb_plugin_data', array( 'ajax_url' => admin_url('admin-ajax.php'), 'post_id' => get_the_ID(), 'seats_sold' => $_hsf_seats_sold, "background_img" => $background_img, 'isAdmin' => ( current_user_can( 'manage_options' ) ? 1 : 0 ) ));

		wp_enqueue_script( 'hsf_tb-script' );

	wp_enqueue_style( 'hsf_tb_bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
	wp_enqueue_style( 'hsf_tb_style', hsf_tb_PLUGIN_URL.'css/style.css' );

wp_enqueue_script( 'bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '', true );

wp_enqueue_style( 'hsf_tb_multi-select-style', hsf_tb_PLUGIN_URL.'css/multi-select.css' );

wp_register_script( 'hsf_tb-multi-select-script', hsf_tb_PLUGIN_URL.'js/jquery.multi-select.js', array( 'jquery' ), '', true );

wp_enqueue_script( 'hsf_tb-multi-select-script' );


	}

  public function modify_wc_product() {

    $hsf_seat = get_post_meta(get_the_ID(), '_hsf_seat_selected', true);

    if (empty($hsf_seat))
      return;

    if ($hsf_seat == "none")
      return;

    $hsf_seat = (int) $hsf_seat;

    //remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    // add_action( 'woocommerce_after_main_content', array( $this, 'add_zone_plans'), 10 );
    add_filter('woocommerce_product_tabs', [$this, 'add_zone_plans']);
  }

  public function add_zone_plans($tabs) {


    $tabs['hsf_seat_plan']['title'] = "HSF Seat Plans";
    $tabs['hsf_seat_plan']['priority'] = 5;
    $tabs['hsf_seat_plan']['callback'] = [$this, 'add_zone_plans_callback'];

    return $tabs;
  }

	private function seating_plans_data_array($postID = "") {
		$post_ID = ( empty($postID) ? get_the_ID() : $postID );
		$titan = TitanFramework::getInstance( 'hsf_tb' );
    $hsf_seat = get_post_meta($post_ID, '_hsf_seat_selected', true);
    $hsf_seat = (int) $hsf_seat;
    $seating_plans = [];
    $seating_plans['zone_a'] = [ 'title' => $titan->getOption( 'zone_a_plan_title', $hsf_seat ), 'plan' => $titan->getOption( 'zone_a_plan', $hsf_seat ), 'plan_data' => $titan->getOption('zone_a_plan_data', $hsf_seat) ];
    $seating_plans['zone_b'] = [ 'title' => $titan->getOption( 'zone_b_plan_title', $hsf_seat ), 'plan' => $titan->getOption( 'zone_b_plan', $hsf_seat ), 'plan_data' => $titan->getOption('zone_b_plan_data', $hsf_seat) ];
    $seating_plans['zone_c'] = [ 'title' => $titan->getOption( 'zone_c_plan_title', $hsf_seat ), 'plan' => $titan->getOption( 'zone_c_plan', $hsf_seat ), 'plan_data' => $titan->getOption('zone_c_plan_data', $hsf_seat) ];
    $seating_plans['zone_d'] = [ 'title' => $titan->getOption( 'zone_d_plan_title', $hsf_seat ), 'plan' => $titan->getOption( 'zone_d_plan', $hsf_seat ), 'plan_data' => $titan->getOption('zone_d_plan_data', $hsf_seat) ];
    $seating_plans['raised_seating_plan'] = [ 'title' => $titan->getOption( 'raised_seating_plan_title', $hsf_seat ), 'plan' => $titan->getOption( 'raised_seating_plan', $hsf_seat ), 'plan_data' => $titan->getOption('raised_seating_plan_data', $hsf_seat) ];

		$seating_plans['general_seating_plan'] = [ 'price' => $titan->getOption( 'general_seating_price', $hsf_seat ), 'qty' => $titan->getOption( 'general_seating_qty', $hsf_seat ), 'title' => 'General Seating Plan'];

		$seating_plans['warning_text_user_deselect'] =  ['title' => 'warning text', 'data' => $titan->getOption( 'warning_text_user_deselect' )];

		return $seating_plans;

	}




  public function add_zone_plans_callback() {

    ob_start();
    $titan = TitanFramework::getInstance( 'hsf_tb' );
    $hsf_seat = get_post_meta(get_the_ID(), '_hsf_seat_selected', true);
    $hsf_seat = (int) $hsf_seat;
    $seating_plans = $this->seating_plans_data_array();

		$default_seat_color = $titan->getOption( 'default_seat_color' );

		$seat_blank = ( file_exists(hsf_tb_PLUGIN_DIR.DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."seat-blank.png") ? hsf_tb_PLUGIN_URL."img/"."seat-blank.png" : "" );

		$zone_b_d_width = $titan->getOption( 'zone_b_d_width', $hsf_seat );
		$zone_b_d_width = (int) $zone_b_d_width;


		$height = $titan->getOption( 'zone_a_c_height', $hsf_seat );
		$height = (int) $height;

    include hsf_tb_PLUGIN_DIR.'template'.DIRECTORY_SEPARATOR.'seating_plan.php';

    $output = ob_get_clean();

    echo $output;

  }


  function hsf_data_tab_data_meta_save( $post_id ){
    $woo_embroidery_select = isset( $_POST['_hsf_seat_selected_group_data'] ) ? $_POST['_hsf_seat_selected_group_data'] : '';
    update_post_meta( $post_id, '_hsf_seat_selected', $woo_embroidery_select );


}


  public function hsf_data_tab( $product_data_tabs ) {
	$product_data_tabs['hsf-seat-tab'] = array(
		'label' => __( 'Select Seating Plan', 'hsf_tb' ),
		'target' => 'custom_hsf_seat-select',
	);
	return $product_data_tabs;
  }

public function hsf_data_tab_data() {
  global $woocommerce, $post;
  ?>
  <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
  <div id="custom_hsf_seat-select" class="panel hsf_tb" style="display: block">
      <?php

global $post;

$args = array(
  'numberposts' => -1,
  'post_type'   => 'hsf_tb_seat_plans'
);

$hsf_tb_seat_plans = get_posts( $args );

$hsf_tb_seat_plans = (empty($hsf_tb_seat_plans) ? array() : $hsf_tb_seat_plans);

$hsf_tb_seat_plans_arr = array();
$hsf_tb_seat_plans_arr['none'] = 'None';

foreach ($hsf_tb_seat_plans as $single_hsf_tb_seat_plans) {
  if (!empty($single_hsf_tb_seat_plans->post_title) && !empty($single_hsf_tb_seat_plans->ID))
    $hsf_tb_seat_plans_arr[$single_hsf_tb_seat_plans->ID] = $single_hsf_tb_seat_plans->post_title;

}


if (!empty($_GET['post']))
  $current_val = get_post_meta( $_GET['post'] , '_hsf_seat_selected', true );
else
  $current_val = '';


  woocommerce_wp_select(array(

      'id'            => '_hsf_seat_selected_group_data',
      'description'	=> __('Select Seating Plan', 'hsf_tb'),
      'wrapper_class' => 'show_if_simple',
      'options'	=> $hsf_tb_seat_plans_arr,
      'value'	=> $current_val

    ));


      ?>

  </div>



  <?php

}

  // Register Custom Post Type
  public function hsf_tb_seat_plans_func() {

	$labels = array(
		'name'                  => _x( 'HSF Seat Plans', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'HSF Seat Plan', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'HSF Seat Plans', 'text_domain' ),
		'name_admin_bar'        => __( 'HSF Seat Plan', 'text_domain' ),
		'archives'              => __( 'HSF Seat Plan Archives', 'text_domain' ),
		'attributes'            => __( 'HSF Seat Plan Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All HSF Seat Plans', 'text_domain' ),
		'add_new_item'          => __( 'Add New HSF Seat Plan', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'HSF Seat Plan', 'text_domain' ),
		'labels'                => $labels,
		// 'supports'              => array( 'title', 'custom-fields', ),
     'supports'              => array( 'title' ),
		'taxonomies'            => array( 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
    'menu_icon'             => 'dashicons-forms',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'hsf_tb_seat_plans', $args );

}

public function meta_boxes() {

  $titan = TitanFramework::getInstance( 'hsf_tb' );

  $zone_a = $titan->createMetaBox( array(
  'name' => 'Zone A',
  'post_type' => array( 'hsf_tb_seat_plans' ),
  ) );


  $zone_b = $titan->createMetaBox( array(
  'name' => 'Zone B',
  'post_type' => array( 'hsf_tb_seat_plans' ),
  ) );


  $zone_c = $titan->createMetaBox( array(
  'name' => 'Zone C',
  'post_type' => array( 'hsf_tb_seat_plans' ),
  ) );


  $zone_d = $titan->createMetaBox( array(
  'name' => 'Zone D',
  'post_type' => array( 'hsf_tb_seat_plans' ),
  ) );

  $raised_seating = $titan->createMetaBox( array(
  'name' => 'Raised Seating',
  'post_type' => array( 'hsf_tb_seat_plans' ),
  ) );

  $zone_a->createOption( array(
  'name' => 'Seating Plans',
  'id' => 'zone_a_plan',
  'type' => 'textarea',
  'desc' => 'Specify number of seats in each row. Use new lines for rows. Example: <br> 10|red<br>20<br>30|yellow<br><br>[color is optional, set default on option page]'
  ) );


	$zone_a->createOption( array(
	'name' => 'Seating Plans Data',
	'id' => 'zone_a_plan_data',
	'type' => 'textarea',
	'desc' => 'Specify row name, price in each row. Use new lines for rows. Example: <br> row_1|10.6<br>My row 2|200<br><br>Please make sure amount of data row matches above Seating Plan rows.'
	) );






  $zone_a->createOption([
    'name' => 'Optional Title',
    'id' => 'zone_a_plan_title',
    'type' => 'text',
    'default' => 'Zone A',
    'desc' => ' Set Optional Title.'
    ]);

		$zone_a->createOption([
	    'name' => 'Max Height For Zone A and Zone C',
	    'id' => 'zone_a_c_height',
	    'type' => 'text',
	    'default' => '',
	    'desc' => 'Set Max Height (in px) For Zone A and Zone C.'
	    ]);

  $zone_b->createOption( array(
  'name' => 'Seating Plans',
  'id' => 'zone_b_plan',
  'type' => 'textarea',
  'desc' => 'Specify number of seats in each row. Use new lines for rows. Example: <br> 10|red<br>20<br>30|yellow<br><br>[color is optional, set default on option page]'
  ) );

	$zone_b->createOption( array(
	'name' => 'Seating Plans Data',
	'id' => 'zone_b_plan_data',
	'type' => 'textarea',
	'desc' => 'Specify row name, price in each row. Use new lines for rows. Example: <br> row_1|10.6<br>My row 2|200<br><br>Please make sure amount of data row matches above Seating Plan rows.'
	) );



  $zone_b->createOption([
    'name' => 'Optional Title',
    'id' => 'zone_b_plan_title',
    'type' => 'text',
    'default' => 'Zone B',
    'desc' => ' Set Optional Title.'
    ]);

			$zone_b->createOption([
		    'name' => 'Max Width For Zone B and Zone D',
		    'id' => 'zone_b_d_width',
		    'type' => 'text',
		    'default' => '',
		    'desc' => 'Set Max Width (in px) For Zone B and Zone D.'
		    ]);


  $zone_c->createOption( array(
  'name' => 'Seating Plans',
  'id' => 'zone_c_plan',
  'type' => 'textarea',
  'desc' => 'Specify number of seats in each row. Use new lines for rows. Example: <br> 10|red<br>20<br>30|yellow<br><br>[color is optional, set default on option page]'
  ) );

	$zone_c->createOption( array(
	'name' => 'Seating Plans Data',
	'id' => 'zone_c_plan_data',
	'type' => 'textarea',
	'desc' => 'Specify row name, price in each row. Use new lines for rows. Example: <br> row_1|10.6<br>My row 2|200<br><br>Please make sure amount of data row matches above Seating Plan rows.'
	) );

  $zone_c->createOption([
    'name' => 'Optional Title',
    'id' => 'zone_c_plan_title',
    'type' => 'text',
    'default' => 'Zone C',
    'desc' => ' Set Optional Title.'
    ]);




  $zone_d->createOption( array(
  'name' => 'Seating Plans',
  'id' => 'zone_d_plan',
  'type' => 'textarea',
  'desc' => 'Specify number of seats in each row. Use new lines for rows. Example: <br> 10|red<br>20<br>30|yellow<br><br>[color is optional, set default on option page]'
  ) );

  $zone_d->createOption([
    'name' => 'Optional Title',
    'id' => 'zone_d_plan_title',
    'type' => 'text',
    'default' => 'Zone D',
    'desc' => ' Set Optional Title.'
    ]);

		$zone_d->createOption( array(
		'name' => 'Seating Plans Data',
		'id' => 'zone_d_plan_data',
		'type' => 'textarea',
		'desc' => 'Specify row name, price in each row. Use new lines for rows. Example: <br> row_1|10.6<br>My row 2|200<br><br>Please make sure amount of data row matches above Seating Plan rows.'
		) );

  $raised_seating->createOption( array(
  'name' => 'Seating Plans',
  'id' => 'raised_seating_plan',
  'type' => 'textarea',
  'desc' => 'Specify number of seats in each row. Use new lines for rows. Example: <br> 10|red<br>20<br>30|yellow<br><br>[color is optional, set default on option page]'
  ) );

  $raised_seating->createOption([
    'name' => 'Optional Title',
    'id' => 'raised_seating_plan_title',
    'type' => 'text',
    'default' => 'Raised Seating',
    'desc' => ' Set Optional Title.'
    ]);

		$raised_seating->createOption( array(
		'name' => 'Seating Plans Data',
		'id' => 'raised_seating_plan_data',
		'type' => 'textarea',
		'desc' => 'Specify row name, price in each row. Use new lines for rows. Example: <br> row_1|10.6<br>My row 2|200<br><br>Please make sure amount of data row matches above Seating Plan rows.'
		) );

		$general_admission = $titan->createMetaBox( array(
	  'name' => 'General Seating',
	  'post_type' => array( 'hsf_tb_seat_plans' ),
	  ) );

		$general_admission->createOption([
	    'name' => 'General Seating Price',
	    'id' => 'general_seating_price',
	    'type' => 'text',
	    'default' => '50.0',
	    'desc' => ' Set General Seating Price'
	    ]);

			$general_admission->createOption([
		    'name' => 'General Seating Quantity',
		    'id' => 'general_seating_qty',
		    'type' => 'text',
		    'default' => '500',
		    'desc' => ' Set General Seating Quantity'
		    ]);

				$clear_sold_seats = $titan->createMetaBox( array(
				'name' => 'Clear Sold Seats',
				'post_type' => array( 'product' ),
				'context' => 'side'
				) );

				$clear_sold_seats->createOption([
			    'type' => 'custom',
			    'custom' => '
					<a class="admin_clear_sold_seats" href="'.admin_url('admin-post.php').'?action=clear_sold_seats&postID='.( empty($_GET['post']) ? "" : $_GET['post'] ).'"><button data-postID="'.$_GET['post'].'">Clear Sold Seats</button></a>

					'
			    ]);


}

}


?>
