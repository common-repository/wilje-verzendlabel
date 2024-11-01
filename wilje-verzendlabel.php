<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wiljeonline.nl
 * @since             1.0.0
 * @package           Wilje_Verzendlabel
 *
 * @wordpress-plugin
 * Plugin Name:       Wilje Verzendlabel
 * Plugin URI:        https://wiljeonline.nl
 * Description:       Connect your Woocommerce shop with the PostNL API using this simple solution provided by Wilje Online!
 * Version:           1.0.0
 * Author:            Daniel Riezebos
 * Author URI:        https://wiljeonline.nl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wilje-verzendlabel
 * Domain Path:       /languages
 */

use Dompdf\Css\Content\StringPart;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WILJE_ONLINE_VERZENDLABEL_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wilje-verzendlabel-activator.php
 */
function wilje_online_verzendlabel_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wilje-verzendlabel-activator.php';
	Wilje_Online_Verzendlabel_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wilje-verzendlabel-deactivator.php
 */
function wilje_online_verzendlabel_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wilje-verzendlabel-deactivator.php';
	Wilje_Online_Verzendlabel_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wilje_online_verzendlabel_activate' );
register_deactivation_hook( __FILE__, 'wilje_online_verzendlabel_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wilje-verzendlabel.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function wilje_online_verzendlabel_run() {
	$plugin = new Wilje_Online_Verzendlabel();
	$plugin->run();

	// To get the translations running
	do_action('plugins_loaded');
}
add_action('woocommerce_shipping_init', 'wilje_online_verzendlabel_run');

function wilje_online_verzendlabel_add_shipping_method($shipping_methods) {	
	$shipping_methods[Class_Wilje_Online_Verzendlabel_Shipping_Method::SETTINGS_ID] = 'Class_Wilje_Online_Verzendlabel_Shipping_Method';	
	return $shipping_methods;
}
add_filter('woocommerce_shipping_methods', 'wilje_online_verzendlabel_add_shipping_method');

function wilje_online_verzendlabel_prepare_stylesheet () {
	 wp_enqueue_style(
		'wilje_online_verzendlabel_style',
		plugin_dir_url(__FILE__) . 'admin/css/wilje-verzendlabel-admin.css',
		[],
		'1.0.0'
	 );
}

function wilje_online_verzendlabel_prepare_javascript() {
	wp_enqueue_script(
		'wilje_online_verzendlabel_url_switch',
		plugin_dir_url(__FILE__) . 'admin/js/url-switch.js',
		[],
		'1.0.0',
		true
	);
}
add_action('admin_enqueue_scripts', 'wilje_online_verzendlabel_prepare_javascript');
add_action('admin_enqueue_scripts', 'wilje_online_verzendlabel_prepare_stylesheet');

function wilje_online_verzendlabel_metabox_callback ($post) {
	$post_id = method_exists($post, 'get_id') ? $post->get_id() : $post->ID;
	$barcode = get_post_meta($post_id, 'postnl_barcode', true);
	$barcode_type = get_post_meta($post_id, 'barcode_type', true);
	?>
		<ul class="order-actions submitbox">			
			<li class="wide">
				<select name="wilje_online_verzendlabel_parcel_options" id="wilje_online_verzendlabel_parcel_options" onchange="url_switch()">
					<option value=""><?php esc_html_e('Choose...', 'wilje-verzendlabel') ?></option>
					<option value="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=2928'), 'print_postnl_label')) ?>"><?php esc_html_e('Letterboxparcel - to 2kg', 'wilje-verzendlabel') ?></option>
					<option value="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=3085'), 'print_postnl_label')) ?>"><?php esc_html_e('Small parcel - to 3kg', 'wilje-verzendlabel') ?></option>
					<option value="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=3085'), 'print_postnl_label')) ?>"><?php esc_html_e('Medium parcel - to 10kg', 'wilje-verzendlabel') ?></option>
					<option value="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=3085'), 'print_postnl_label')) ?>"><?php esc_html_e('Large parcel - to 23kg', 'wilje-verzendlabel') ?></option>
				</select>
				<?php if(!empty($barcode)): ?>
					<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=2928'), 'print_postnl_label')) ?>" id="wilje_online_verzendlabel_print_label" class="button" target="_blank" alt="" onclick="return confirm('<?php esc_html_e('Label has already been made, are you sure you want to relabel?', 'wilje-verzendlabel') ?>');">
						<?php esc_html_e('Print PostNL Label', 'wilje-verzendlabel') ?>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width: 14px;height: 14px;line-height: 30px;margin-left: 4px;margin-bottom: 3px;fill: #2aad2a;vertical-align: middle;">
							<path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path>
						</svg>
					</a>
				<?php else: ?>
					<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption=2928'), 'print_postnl_label')) ?>" id="wilje_online_verzendlabel_print_label" class="button exists" target="_blank" alt="">
						<?php esc_html_e('Print PostNL Label', 'wilje-verzendlabel') ?>
					</a>
				<?php endif; ?>	
			</li>			
			<li class="wide">
				<?php if(!empty($barcode)): ?>
					<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=print_postnl_label&order_id='.$post_id.'&parceloption='.$barcode_type.'&barcode='.$barcode), 'print_postnl_label')) ?>" id="wilje_online_verzendlabel_download_label" class="button" target="_blank" alt="">
						<?php esc_html_e('Download PostNL Label', 'wilje-verzendlabel') ?>
					</a>
				<?php else: ?>
					<input disabled type="submit" class="button" id="wilje_online_verzendlabel_download_label" value="<?php esc_html_e('Download PostNL Label', 'wilje-verzendlabel') ?>" />
				<?php endif; ?>
			</li>	
			<li class="wide">
				<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=send_track_trace&order_id='.$post_id), 'send_track_trace')) ?>" id="wilje_online_verzendlabel_send_track_trace" class="button exists" onclick="return confirm('<?php esc_html_e('Are you sure you want to send the Track & Trace email?', 'wilje-verzendlabel') ?>');">
					<?php esc_html_e('Send Track & Trace Email', 'wilje-verzendlabel') ?>
				</a>				
			</li>			
		</ul>
	<?php
};

function wilje_online_verzendlabel_add_metabox() {
	add_meta_box(
		'wilje_online_verzendlabel_metabox',
		'Wilje Verzendlabel',
		'wilje_online_verzendlabel_metabox_callback',
		get_current_screen()->id,
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wilje_online_verzendlabel_add_metabox' );

function wilje_online_verzendlabel_print_label_action_execute()
{
	if (!isset($_GET[ '_wpnonce' ]) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET[ '_wpnonce' ])), 'print_postnl_label')) {
		return;
	}

	require_once __DIR__ . '/includes/class-wilje-verzendlabel-connector.php';
	require_once __DIR__ . '/includes/class-wilje-verzendlabel-shipping-method.php';	
	$connector = new Class_Wilje_Online_Verzendlabel_Connector(new Class_Wilje_Online_Verzendlabel_Shipping_Method());

	try {	
		$order_id = null;
		if (isset($_GET['order_id'])){
			$order_id = sanitize_key($_GET['order_id']);
		}
		$order = wc_get_order($order_id);
		if (!$order) {
			throw new \Exception('Order cannot be found with ID: '.$order_id);
		}

		$label_data = $connector->get_verzendlabel_label($order);
		$content = $label_data['ResponseShipments'][0]['Labels'][0]['Content'] ?? false;
		if (!$content) {
			throw new \Exception('Error: Content is empty');
		}

		update_post_meta($order->get_id(), 'postnl_barcode', $label_data['ResponseShipments'][0]['Barcode']);
		update_post_meta($order->get_id(), 'barcode_type', $label_data['ResponseShipments'][0]['ProductCodeDelivery']);
		$order->add_order_note('Barcode has been generated: ' . $label_data['ResponseShipments'][0]['Barcode']);
		
		$pdf_content = base64_decode($content);		
		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="filename.pdf"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($pdf_content));

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Binary PDF data, escaping not applicable.
    	echo $pdf_content;
		exit;
	} catch (\Exception $e) {
		error_log(print_r($e->getMessage(), true));
		exit;
	}
}
add_action('wp_ajax_print_postnl_label', 'wilje_online_verzendlabel_print_label_action_execute');

function wilje_online_send_track_trace_mail($orderId = null)
{
	$order = $orderId ? wc_get_order($orderId) : wc_get_order(get_post()->ID);
	if (is_null($order)) {
		return; // TODO: Make some error handling here 
	}
	global $woocommerce;
	$mailer = $woocommerce->mailer();

	$message = $mailer->wrap_message(sprintf( __( 'Track & Trace', 'wilje-verzendlabel' )), sprintf(
		/* translators: %1$s: URL, generated from plugin. %2$s: Site URL, generated from plugin. */
		__('Greetings! <br><br> Your PostNL Track & Trace code can be found by following this link: %1$s <br><br>Thank you for shopping with %2$s!', 'wilje-verzendlabel'),
		wilje_online_get_barcode_url($orderId),
		get_bloginfo('wpurl')
	));
	if ($mailer->send( $order->billing_email, sprintf( __( 'Track & Trace', 'wilje-verzendlabel' ), $order->get_order_number() ), $message )) {
		wp_admin_notice('Track & Trace has been sent');
		wp_safe_redirect(add_query_arg('email_sent', 'true', wp_get_referer()));
		return;
	}
}

function wilje_online_track_add_trace_email_action()
{	
	if (!isset($_GET[ '_wpnonce' ]) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET[ '_wpnonce' ])), 'send_track_trace')) {
		return;
	}

	if (!isset($_GET['order_id'])) {
		return;
	}

	wilje_online_send_track_trace_mail(sanitize_key(wp_unslash($_GET['order_id'])));
}
add_action('wp_ajax_send_track_trace', 'wilje_online_track_add_trace_email_action');
add_action('woocommerce_order_status_completed', 'wilje_online_send_track_trace_mail');

function wilje_online_verzendlabel_add_barcode_column_header($columns)
{
	if (!array_key_exists('wc_actions', $columns)) {
		return;
	}
	$wc_actions = $columns['wc_actions'];
	unset( $columns['wc_actions'] );

	$columns['wilje_online_verzendlabel_tracking_link'] = esc_html__( 'Wilje Online | Shipping Tracking', 'wilje-verzendlabel' );
	$columns['wc_actions'] = $wc_actions;

	return $columns;
}

function wilje_online_get_barcode_url($post_id) : string
{	
	if (!$order = wc_get_order($post_id)) {
		return '';
	}

	return esc_url(add_query_arg(
		[
			'B' => get_post_meta($order->get_id(), 'postnl_barcode', true),
			'P' => $order->get_shipping_postcode(),
			'D' => $order->get_shipping_country(),
			'T' => 'C'
		],
		'https://postnl.nl/tracktrace/' 
	));
}

function wilje_online_verzendlabel_add_barcode_column_content($column) 
{
	if ($column == 'wilje_online_verzendlabel_tracking_link') {		
		$order = wc_get_order(get_post()->ID);
		$barcode = $order->get_meta('postnl_barcode');		
		echo sprintf('<a href="%1$s" target="_blank" class="wilje-verzendlabel-tracking-link">%2$s</a>', esc_url(wilje_online_get_barcode_url($order)), esc_html($barcode));
	}	
}

// 🥲
add_filter( 'manage_edit-shop_order_columns', 'wilje_online_verzendlabel_add_barcode_column_header');
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'wilje_online_verzendlabel_add_barcode_column_header');
add_action( 'manage_shop_order_posts_custom_column', 'wilje_online_verzendlabel_add_barcode_column_content');
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'wilje_online_verzendlabel_add_barcode_column_content');
