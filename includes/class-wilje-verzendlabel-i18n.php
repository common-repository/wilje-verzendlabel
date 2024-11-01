<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://wiljeonline.nl
 * @since      1.0.0
 *
 * @package    Wilje_Online_Verzendlabel
 * @subpackage Wilje_Online_Verzendlabel/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wilje_Online_Verzendlabel
 * @subpackage Wilje_Online_Verzendlabel/includes
 * @author     Daniel Riezebos <daniel@wiljeonline.nl>
 */
class Wilje_Online_Verzendlabel_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wilje-verzendlabel',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
